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

use FedEx\CrossBorder\Model\Refund\SenderFactory;
use FedEx\CrossBorder\Model\Refund\Sender;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\CollectionFactory as PackNotificationCollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory as CreditmemoItemCollectionFactory;

class RefundManagement
{
    const ERROR_CANT_SEND           = 'Can\'t refund for an empty product list.';
    const ERROR_PRODUCT_IDENTIFIER  = 'Incorrect product identifier (ID = %1)';
    const DEFAULT_REASON_CODE       = 1;

    /**
     * @var CreditmemoItemCollectionFactory
     */
    protected $_creditmemoItemCollectionFactory;

    /**
     * @var PackNotificationCollectionFactory
     */
    protected $_packNotificationCollectionFactory;

    /**
     * @var ProductValidator
     */
    protected $_productValidator;

    /**
     * @var SenderFactory
     */
    protected $_senderFactory;

    /**
     * RefundManagement constructor.
     *
     * @param CreditmemoItemCollectionFactory $creditmemoItemCollectionFactory
     * @param PackNotificationCollectionFactory $packNotificationCollectionFactory
     * @param ProductValidator $productValidator
     * @param SenderFactory $senderFactory
     */
    public function __construct(
        CreditmemoItemCollectionFactory $creditmemoItemCollectionFactory,
        PackNotificationCollectionFactory $packNotificationCollectionFactory,
        ProductValidator $productValidator,
        SenderFactory $senderFactory
    ) {
        $this->_creditmemoItemCollectionFactory = $creditmemoItemCollectionFactory;
        $this->_packNotificationCollectionFactory = $packNotificationCollectionFactory;
        $this->_productValidator = $productValidator;
        $this->_senderFactory = $senderFactory;
    }

    /**
     * Returns product identifier value
     *
     * @param Product $product
     * @return mixed
     * @throws LocalizedException
     */
    protected function _getProductIdentifierValue(Product $product)
    {
        if (!$this->_productValidator->validateIdentifier($product)) {
            throw new LocalizedException(
                __(static::ERROR_PRODUCT_IDENTIFIER, $product->getId())
            );
        }

        $code = $this->_productValidator->getHelper()->getProductIdentifier();
        return $product->getData($code);
    }

    /**
     * Sending refund request
     *
     * @param CreditmemoInterface $creditmemo
     * @return $this
     * @throws \Exception
     */
    public function send(CreditmemoInterface $creditmemo)
    {
        if ($creditmemo->getOrder() && $creditmemo->getOrder()->getExtensionAttributes()) {
            $orderLink = $creditmemo->getOrder()->getExtensionAttributes()->getFdxcbData();
            if ($orderLink && $orderLink->getId()) {
                $data = [
                    "order_number"          => $orderLink->getFxcbOrderNumber(),
                    "refund_reason_code"    => static::DEFAULT_REASON_CODE,
                    "items"                 => []
                ];

                $refundedItems = [];
                $collection = $this->_creditmemoItemCollectionFactory->create()->setCreditmemoFilter(
                    $creditmemo->getId()
                );

                foreach ($collection as $item) {
                    $data['items'][] = [
                        'product_id'    => $this->_getProductIdentifierValue($item->getOrderItem()->getProduct()),
                        'quantity'      => $item->getQty(),
                    ];
                    $refundedItems[$item->getProductId()] = $item->getQty();
                }

                if (count($data['items'])) {
                    $collection = $this->_packNotificationCollectionFactory->create();
                    $collection->addFieldToFilter(
                        PackNotification::ORDER_ID,
                        $creditmemo->getOrder()->getId()
                    );

                    /** @var PackNotification $packNotification */
                    foreach ($collection as $packNotification) {
                        $packNotification->removeRefundedItems($refundedItems);
                    }

                    /** @var Sender $sender */
                    $sender = $this->_senderFactory->create();
                    $sender->addHeader(
                        'Content-Type',
                        'application/json'
                    )->addData(
                        json_encode($data)
                    )->getResponse(Sender::METHOD_POST);

                    if ($sender->hasError()) {
                        throw new LocalizedException(
                            __($sender->getErrorMessage())
                        );
                    }
                } else {
                    throw new LocalizedException(
                        __(static::ERROR_CANT_SEND)
                    );
                }
            }
        }

        return $this;
    }
}
