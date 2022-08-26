<?php
/**
 * FedEx Cross Border component
 *
 * @category    FedEx
 * @package     FedEx_CrossBorder
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\CrossBorder\Model;

use FedEx\Core\Model\Tools;
use FedEx\CrossBorder\Api\Data\OrderLinkInterface;
use FedEx\CrossBorder\Helper\PackNotification as Helper;
use FedEx\CrossBorder\Model\Carrier\Shipping;
use FedEx\CrossBorder\Model\MerchantControl;
use FedEx\CrossBorder\Model\PackNotification\Box;
use FedEx\CrossBorder\Model\PackNotification\Box\Item as BoxItem;
use FedEx\CrossBorder\Model\PackNotification\Sender;
use FedEx\CrossBorder\Model\PackNotification\SenderFactory;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification as ResourceModel;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\Collection as BoxCollection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory as ResponseFileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class PackNotification extends AbstractModel
{
    const ORDER_ID                  = 'order_id';
    const EXTERNAL_ID               = 'external_id';
    const RETAILER_PACK_ID          = 'retailer_pack_id';
    const TRACKING_NUMBER           = 'tracking_number';
    const DIMENSION_UNIT            = 'dimension_unit';
    const WEIGHT_UNIT               = 'weight_unit';
    const DOCUMENT_URL              = 'document_url';
    const CANCEL_URL                = 'cancel_url';
    const STATUS                    = 'status';
    const STATE                     = 'state';

    const STATE_NEW                 = 'new';
    const STATE_SENT                = 'sent';
    const STATE_CANCELED            = 'canceled';

    const STATUS_NEW                = 'new';
    const STATUS_SENT               = 'sent';
    const STATUS_CANCELED           = 'canceled';
    const STATUS_ERROR              = 'error';

    const DOWNLOAD_FILENAME         = 'Pack_Notification_Documents_%s-%s.zip';

    const ERROR_CANT_BE_SEND        = 'The pack notification can\'t be send.';
    const ERROR_CANT_DOWNLOAD       = 'Documents can\'t be downloaded';
    const ERROR_DOCUMENTS_NOT_READY = 'Documents not ready';


    /**
     * @var Box[]
     */
    protected $_boxes       = [];

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var array
     */
    protected $_mapping = [
        'transit_method_code'   => [
            Shipping::CODE . '_' . Shipping::METHOD_EXPRESS     => 'FDX-IP',
            Shipping::CODE . '_' . Shipping::METHOD_STANDARD    => 'FDX-IP',
            Shipping::CODE . '_' . Shipping::METHOD_ECONOMY     => 'FDX-IE',
        ],
        'pickup_address'        => [
            'company_name'      => 'shipping/origin/name',
            'address1'          => 'shipping/origin/street_line1',
            'address2'          => 'shipping/origin/street_line2',
            'city'              => 'shipping/origin/city',
            'state'             => 'shipping/origin/region_id',
            'zip_code'          => 'shipping/origin/postcode',
            'country'           => 'shipping/origin/country_id',
            'phone'             => 'shipping/origin/phone',
            'tin'               => '',
        ],
        'consignor_address'     => [
            'company_name'      => 'general/store_information/name',
            'address1'          => 'general/store_information/street_line1',
            'address2'          => 'general/store_information/street_line2',
            'city'              => 'general/store_information/city',
            'state'             => 'general/store_information/region_id',
            'zip_code'          => 'general/store_information/postcode',
            'country'           => 'general/store_information/country_id',
            'phone'             => 'general/store_information/phone',
            'tin'               => '',
        ],
        'response'              => [
            'external_id'       => 'id_pack_notification',
            'retailer_pack_id'  => 'retailer_pack_identifier',
            'tracking_number'   => 'tracking_number',
            'document_url'      => 'documents_url',
            'cancel_url'        => 'cancel_url',
        ],
    ];

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var ResponseFileFactory
     */
    protected $_responseFileFactory;

    /**
     * @var SenderFactory
     */
    protected $_senderFactory;

    /**
     * PackNotification constructor.
     *
     * @param Helper $helper
     * @param OrderRepositoryInterface $orderRepository
     * @param ResponseFileFactory $responseFileFactory
     * @param SenderFactory $senderFactory
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Helper $helper,
        OrderRepositoryInterface $orderRepository,
        ResponseFileFactory $responseFileFactory,
        SenderFactory $senderFactory,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_orderRepository = $orderRepository;
        $this->_responseFileFactory = $responseFileFactory;
        $this->_senderFactory = $senderFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Returns address data
     *
     * @param string $name
     * @return array
     */
    protected function _getAddressData($name)
    {
        $result = [];

        if (isset($this->_mapping[$name])) {
            foreach ($this->_mapping[$name] as $key => $path) {
                $result[$key] = (!empty($path) ? $this->getHelper()->getConfig($path) : '');
                if ($key == 'state') {
                    $result[$key] = $this->getHelper()->getRegionCodeById($result[$key]);
                }
            }
        }

        return $result;
    }

    /**
     * Returns boxes info
     *
     * @return array
     */
    protected function _getBoxesInfo()
    {
        $result = [];

        foreach ($this->getBoxCollection() as $box) {
            $result[] = [
                'box_width'         => $box->getWidth(),
                'box_height'        => $box->getHeight(),
                'box_length'        => $box->getLength(),
                'box_weight'        => $box->getWeight(),
                'box_dim_uom'       => $this->getDimensionUnit(),
                'box_weight_uom'    => $this->getWeightUnit(),
                'items'             => $this->_getBoxItems($box),
            ];
        }

        return $result;
    }

    /**
     * Returns box items
     *
     * @param Box $box
     * @return array
     */
    protected function _getBoxItems(Box $box)
    {
        $result = [];

        if ($box instanceof Box) {
            foreach ($box->getItemCollection() as $item) {
                for ($i = 0; $i < $item->getQty(); $i++) {
                    $result[] = [
                        'product_id'    => $item->getProductIdentifier(),
                        'coo'           => $item->getCountryOfOrigin(),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Returns document data
     *
     * @param array $data
     * @return array|string
     * @throws LocalizedException
     */
    protected function _getDocument($data)
    {
        if (isset($data['base64'])) {
            $result = base64_decode($data['base64']);
        } else {
            /** @var Sender $sender */
            $sender = $this->_senderFactory->create();
            $result = $sender->getDocument($data['url']);
            if ($sender->hasError()) {
                throw new LocalizedException(
                    __($sender->getErrorMessage())
                );
            }
        }

        return $result;
    }

    /**
     * Returns transit method code
     *
     * @return string
     */
    protected function _getTransitMethodCode()
    {
        if ($this->getOrder() && $shippingMethod = $this->getOrder()->getShippingMethod()) {
            if (!empty($this->_mapping['transit_method_code'][$shippingMethod])) {
                return $this->_mapping['transit_method_code'][$shippingMethod];
            }
        }

        return '';
    }

    /**
     * Parsing response data
     *
     * @param array $response
     * @return array
     */
    protected function _parseResponse($response)
    {
        $result = [];
        foreach ($this->_mapping['response'] as $key => $_key) {
            if (!empty($response[$_key])) {
                $result[$key] = $response[$_key];
            }
        }

        return $result;
    }

    /**
     * Preparing cancel data
     *
     * @return array
     */
    protected function _prepareCancelData()
    {
        return json_encode([
            'type'  => 'cancel',
            'data'  => [
                'id' => $this->getExternalId(),
            ],
        ]);
    }

    /**
     * Preparing download data
     *
     * @return array
     */
    protected function _prepareDownloadData()
    {
        return json_encode([
            'type'  => 'download',
            'data'  => [
                'id' => $this->getExternalId(),
            ],
        ]);
    }

    /**
     * Preparing zip file with all documents for downloading
     *
     * @param $data
     * @return false|ResponseInterface
     * @throws LocalizedException
     * @throws \Exception
     */
    protected function _prepareDownloadResponseData($data)
    {
        if (is_array($data)) {
            $path = DirectoryList::VAR_DIR . '/tmp';
            if (!Tools::checkDir(BP . '/' . $path)) {
                throw new LocalizedException(
                    __(static::ERROR_CANT_DOWNLOAD)
                );
            }

            $filename = Tools::uuid() . '.zip';
            $zip = new \ZipArchive();
            if (!$zip->open(BP . '/' . $path . '/' . $filename, \ZipArchive::CREATE)) {
                throw new LocalizedException(
                    __(static::ERROR_CANT_DOWNLOAD)
                );
            }

            foreach ($data as $item) {
                $zip->addFromString(
                    sprintf('%s_%s.%s',
                        $item['doc_name'],
                        $item['id_doc'],
                        $item['format']
                    ),
                    $this->_getDocument($item)
                );
            }
            $zip->close();

            return $this->_responseFileFactory->create(
                sprintf(
                    static::DOWNLOAD_FILENAME,
                    $this->getOrder()->getIncrementId(),
                    $this->getId()
                ),
                [
                    'type'  => "filename",
                    'value' => $path . '/' . $filename,
                    'rm'    => true,
                ],
                DirectoryList::ROOT,
                'application/zip'
            );
        }

        return false;
    }

    /**
     * Preparing sending data
     *
     * @return array
     */
    protected function _prepareSendingData()
    {
        /** @var OrderLinkInterface $orderLink */
        $orderLink = $this->getHelper()->getOrderLink(
            $this->getOrder()
        );

        $boxesInfo = $this->_getBoxesInfo();

        return json_encode([
            'type'  => 'create',
            'data'  => [
                'fxcb_order_number'     => ($orderLink ? $orderLink->getFxcbOrderNumber() : ''),
                'transit_method_code'   => $this->_getTransitMethodCode(),
                'pickup_address'        => $this->_getAddressData('pickup_address'),
                'consignor_address'     => $this->_getAddressData('consignor_address'),
                'boxes_info'            => $boxesInfo,
                'qty_boxes'             => count($boxesInfo),
                'label_format'          => 'PNG',
            ],
            'carrier'                   => $this->getMerchantControl()->getAccountType(),
        ]);
    }

    /**
     * Checks if pack notification can be changed
     *
     * @return bool
     */
    public function canChange()
    {
        return (!$this->getId() || in_array($this->getState(), [static::STATE_NEW, static::STATE_CANCELED]));
    }

    /**
     * Checks if pack notification can be canceled
     *
     * @return bool
     */
    public function canCancel()
    {
        return ($this->getId() && $this->getState() == static::STATE_SENT && $this->getExternalId());
    }

    /**
     * Sending pack notification
     *
     * @return $this
     * @throws LocalizedException
     */
    public function cancel()
    {
        if ($this->canCancel()) {
            /** @var Sender $sender */
            $sender = $this->_senderFactory->create();
            $sender->addHeader(
                'Content-Type',
                'application/json'
            )->addData(
                $this->_prepareCancelData()
            );

            $response = $sender->getResponse(Sender::METHOD_POST);
            if (!$sender->hasError()) {
                $this->addData(
                    $this->_parseResponse($response)
                )->setState(
                    static::STATE_CANCELED
                )->setStatus(
                    static::STATUS_CANCELED
                )->save();
            } else {
                $this->setStatus(static::STATUS_ERROR)->save();
                throw new LocalizedException(
                    __($sender->getErrorMessage())
                );
            }
        } else {
            throw new LocalizedException(
                __(static::ERROR_CANT_BE_SEND)
            );
        }

        return $this;
    }

    /**
     * Checks if pack notification can be downloaded
     *
     * @return bool
     */
    public function canDownload()
    {
        return ($this->canCancel() && $this->getOrder()->getStatus() == OrderStatusManagement::STATUS_READY_FOR_EXPORT);
    }

    /**
     * Checks if pack notification can be send
     *
     * @return bool
     */
    public function canSend()
    {
        return ($this->getId() &&
            in_array($this->getState(), [static::STATE_NEW, static::STATE_CANCELED])
        );
    }

    /**
     * Downloading pack notification
     *
     * @return false|ResponseInterface
     * @throws LocalizedException
     */
    public function download()
    {
        if ($this->canDownload()) {
            /** @var Sender $sender */
            $sender = $this->_senderFactory->create();
            $sender->addHeader(
                'Content-Type',
                'application/json'
            )->addData(
                $this->_prepareDownloadData()
            );

            $response = $sender->getResponse(Sender::METHOD_POST);
            if (!$sender->hasError()) {
                return $this->_prepareDownloadResponseData($response);
            } else {
                throw new LocalizedException(
                    __($sender->getErrorMessage())
                );
            }
        } else {
            throw new LocalizedException(
                __(static::ERROR_DOCUMENTS_NOT_READY)
            );
        }
    }

    /**
     * Returns order
     *
     * @return Order
     */
    public function getOrder()
    {
        if (!$this->_order instanceof Order) {
            $this->_order = $this->_orderRepository->get($this->getOrderId());
        }

        return $this->_order;
    }

    /**
     * Returns order id
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(static::ORDER_ID);
    }

    /**
     * Sets order id
     *
     * @param int $value
     * @return $this
     */
    public function setOrderId($value)
    {
        return $this->setData(static::ORDER_ID, $value);
    }

    /**
     * Returns external id
     *
     * @return string
     */
    public function getExternalId()
    {
        return $this->getData(static::EXTERNAL_ID);
    }

    /**
     * Sets external id
     *
     * @param string $value
     * @return $this
     */
    public function setExternalId($value)
    {
        return $this->setData(static::EXTERNAL_ID, $value);
    }

    /**
     * Returns retailer pack id
     *
     * @return string
     */
    public function getRetailerPackId()
    {
        return $this->getData(static::RETAILER_PACK_ID);
    }

    /**
     * Sets retailer pack id
     *
     * @param string $value
     * @return $this
     */
    public function setRetailerPackId($value)
    {
        return $this->setData(static::RETAILER_PACK_ID, $value);
    }

    /**
     * Returns tracking number
     *
     * @return string
     */
    public function getTrackingNumber()
    {
        return $this->getData(static::TRACKING_NUMBER);
    }

    /**
     * Sets tracking number
     *
     * @param string $value
     * @return $this
     */
    public function setTrackingNumber($value)
    {
        return $this->setData(static::TRACKING_NUMBER, $value);
    }

    /**
     * Returns dimension unit
     *
     * @return string
     */
    public function getDimensionUnit()
    {
        return $this->getData(static::DIMENSION_UNIT);
    }

    /**
     * Sets dimension unit
     *
     * @param string $value
     * @return $this
     */
    public function setDimensionUnit($value)
    {
        return $this->setData(static::DIMENSION_UNIT, $value);
    }

    /**
     * Returns weight unit
     *
     * @return string
     */
    public function getWeightUnit()
    {
        return $this->getData(static::WEIGHT_UNIT);
    }

    /**
     * Sets weight unit
     *
     * @param string $value
     * @return $this
     */
    public function setWeightUnit($value)
    {
        return $this->setData(static::WEIGHT_UNIT, $value);
    }

    /**
     * Returns document url
     *
     * @return string
     */
    public function getDocumentUrl()
    {
        return $this->getData(static::DOCUMENT_URL);
    }

    /**
     * Sets document url
     *
     * @param string $value
     * @return $this
     */
    public function setDocumentUrl($value)
    {
        return $this->setData(static::DOCUMENT_URL, $value);
    }

    /**
     * Returns cancel url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getData(static::CANCEL_URL);
    }

    /**
     * Sets cancel url
     *
     * @param string $value
     * @return $this
     */
    public function setCancelUrl($value)
    {
        return $this->setData(static::CANCEL_URL, $value);
    }

    /**
     * Returns status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(static::STATUS);
    }

    /**
     * Sets status
     *
     * @param string $value
     * @return $this
     */
    public function setStatus($value)
    {
        return $this->setData(static::STATUS, $value);
    }

    /**
     * Returns state
     *
     * @return string
     */
    public function getState()
    {
        return $this->getData(static::STATE);
    }

    /**
     * Sets state
     *
     * @param string $value
     * @return $this
     */
    public function setState($value)
    {
        return $this->setData(static::STATE, $value);
    }

    /**
     * Adds box
     *
     * @param Box $box
     * @return $this
     */
    public function addBox(Box $box)
    {
        $this->_hasDataChanges = true;
        $box->setPackNotificationId($this->getId());
        $this->_boxes[] = $box;

        return $this;
    }

    /**
     * Returns box collection
     *
     * @return BoxCollection
     */
    public function getBoxCollection()
    {
        return $this->getResource()->getBoxCollection($this);
    }

    /**
     * Returns boxes
     *
     * @return Box[]
     */
    public function getBoxes()
    {
        return $this->_boxes;
    }

    /**
     * Sets boxes
     *
     * @param Box[] $boxes
     * @return $this
     */
    public function setBoxes($boxes)
    {
        $this->_hasDataChanges = true;
        $this->_boxes = $boxes;

        return $this;
    }

    /**
     * Returns helper
     *
     * @return Helper
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Returns merchant control model
     *
     * @return MerchantControl
     */
    public function getMerchantControl()
    {
        return $this->getHelper()->getMerchantControl();
    }

    /**
     * Mark package notification as sent
     *
     * @return $this
     */
    public function markAsSent()
    {
        $this->setState(
            static::STATE_SENT
        )->setStatus(
            static::STATUS_SENT
        )->save();

        return $this;
    }

    /**
     * Remove refunded items from boxes
     * @param array $ids
     * @return $this
     * @throws LocalizedException
     */
    public function removeRefundedItems(&$ids)
    {
        $isCancelSent = false;
        if (!empty($ids) && is_array($ids)) {
            $boxQty = count($this->getBoxCollection());
            /** @var Box $box */
            foreach ($this->getBoxCollection() as $box) {
                $itemsQty = 0;
                /** @var BoxItem $item */
                foreach ($box->getItemCollection() as $item) {
                    $productId = $item->getProductId();
                    $qty = $item->getQty();
                    if (isset($ids[$productId]) && $ids[$productId]) {
                        if (!$isCancelSent && $this->canCancel()) {
                            $this->cancel();
                            $isCancelSent = true;
                        }

                        if ($qty > $ids[$productId]) {
                            $qty -= $ids[$productId];
                            unset($ids[$productId]);
                            $item->setQty(
                                $qty
                            )->save();
                        } else {
                            if ($qty < $ids[$productId]) {
                                $ids[$productId] -= $qty;
                            } else {
                                unset($ids[$productId]);
                            }
                            $qty = 0;
                            $item->delete();
                        }
                    }

                    $itemsQty += $qty;
                }

                if (!$itemsQty) {
                    $box->delete();
                    $boxQty--;
                }

                if (empty($ids)) {
                    break;
                }
            }

            if ($boxQty < 1) {
                $this->delete();
            }
        }

        return $this;
    }

    /**
     * Sending pack notification
     *
     * @return $this
     * @throws LocalizedException
     */
    public function send()
    {
        if ($this->canSend()) {
            /** @var Sender $sender */
            $sender = $this->_senderFactory->create();
            $sender->addHeader(
                'Content-Type',
                'application/json'
            )->addData(
                $this->_prepareSendingData()
            );

            $response = $sender->getResponse(Sender::METHOD_POST);
            if (!$sender->hasError()) {
                $this->addData(
                    $this->_parseResponse($response)
                )->setState(
                    static::STATE_SENT
                )->setStatus(
                    static::STATUS_SENT
                )->save();

                // Change order status to "Ready for Export" if status is AVAILABLE
                if ($response['status'] == 'AVAILABLE') {
                    $this->getOrder()->setState(
                        Order::STATE_PROCESSING
                    )->setStatus(
                        OrderStatusManagement::STATUS_READY_FOR_EXPORT
                    )->save();
                }
            } else {
                $this->setStatus(static::STATUS_ERROR)->save();
                throw new LocalizedException(
                    __($sender->getErrorMessage())
                );
            }
        } else {
            throw new LocalizedException(
                __(static::ERROR_CANT_BE_SEND)
            );
        }

        return $this;
    }
}
