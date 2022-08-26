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
namespace FedEx\CrossBorder\Helper;

use FedEx\Core\Helper\AbstractHelper;
use FedEx\CrossBorder\Api\Data\OrderLinkInterface;
use FedEx\CrossBorder\Model\MerchantControl;
use FedEx\CrossBorder\Model\PackNotificationFactory;
use FedEx\CrossBorder\Model\PackNotification\Box;
use FedEx\CrossBorder\Model\PackNotification\BoxFactory;
use Magento\Backend\Model\UrlInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class PackNotification extends AbstractHelper
{
    const ERROR_PACK_NOTIFICATION_NOT_EXIST     = 'The pack notification for this order doesn\'t exist';

    /**
     * @var int
     */
    protected $_currentPackNotificationId;

    /**
     * @var Box
     */
    protected $_box;

    /**
     * @var BoxFactory
     */
    protected $_boxFactory;

    /**
     * @var MerchantControl
     */
    protected $_merchantControl;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var int
     */
    protected $_orderId;
    /**
     * @var \FedEx\CrossBorder\Model\PackNotification
     */
    protected $_packNotification;

    /**
     * @var PackNotificationFactory
     */
    protected $_packNotificationFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * PackNotification constructor.
     *
     * @param BoxFactory $boxFactory
     * @param MerchantControl $merchantControl
     * @param PackNotificationFactory $packNotificationFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param RegionFactory $regionFactory
     * @param UrlInterface $url
     * @param Context $context
     */
    public function __construct(
        BoxFactory $boxFactory,
        MerchantControl $merchantControl,
        PackNotificationFactory $packNotificationFactory,
        OrderRepositoryInterface $orderRepository,
        RegionFactory $regionFactory,
        UrlInterface $url,
        Context $context
    ) {
        $this->_boxFactory = $boxFactory;
        $this->_merchantControl = $merchantControl;
        $this->_packNotificationFactory = $packNotificationFactory;
        $this->_orderRepository = $orderRepository;
        $this->_regionFactory = $regionFactory;
        parent::__construct($context);
        $this->_urlBuilder = $url;
    }

    /**
     * Checks if pack notification functionality are available for the order
     *
     * @param Order $order
     * @return bool
     */
    public function isAvailable(Order $order)
    {
        $orderLink = $this->getOrderLink($order);
        return $this->getMerchantControl()->isEnabled() &&
            $order->hasInvoices() &&
            $orderLink && $orderLink->getFxcbOrderNumber();
    }

    /**
     * Returns box
     *
     * @param int $id
     * @return Box
     */
    public function getBox($id = null)
    {
        if (!isset($this->_box) || (isset($id) && $this->_box->getId() != $id)) {
            /** @var Box $box */
            $this->_box = $this->_boxFactory->create();
            if (isset($id)) {
                $this->_box->load((int) $id);
            }

            if (!$this->_box->getId()) {
                $this->_box->setPackNotificationId(
                    $this->getCurrentPackNotificationId()
                );
            }
        }

        return $this->_box;
    }

    /**
     * Returns current pack notification
     *
     * @return \FedEx\CrossBorder\Model\PackNotification
     * @throws NoSuchEntityException
     */
    public function getCurrentPackNotification()
    {
        if (!isset($this->_packNotification)) {
            if ($this->getCurrentPackNotificationId()) {
                $this->_packNotification = $this->_packNotificationFactory->create()->load(
                    $this->getCurrentPackNotificationId()
                );

                if (!$this->_packNotification->getId()) {
                    throw new NoSuchEntityException(
                        __(static::ERROR_PACK_NOTIFICATION_NOT_EXIST)
                    );
                }
            } else {
                $this->_packNotification = $this->getNewPackNotification();
            }
        }

        return $this->_packNotification;
    }

    /**
     * Returns current pack notification id
     *
     * @return int
     */
    public function getCurrentPackNotificationId()
    {
        if (!isset($this->_currentPackNotificationId)) {
            $this->_currentPackNotificationId = (int) $this->_request->getParam('pack_id');
        }

        return $this->_currentPackNotificationId;
    }

    /**
     * Returns current order
     *
     * @return Order
     */
    public function getCurrentOrder()
    {
        if (!$this->_order instanceof Order) {
            $this->_order = $this->_orderRepository->get($this->getOrderId());
        }

        return $this->_order;
    }

    /**
     * Returns dimension unit
     *
     * @return string
     */
    public function getDimensionUnit()
    {
        return $this->getConfig('general/locale/dimension_unit');
    }

    /**
     * Returns id value
     *
     * @return int
     */
    public function getId()
    {
        return (int) $this->_getRequest()->getParam('id');
    }

    /**
     * Returns merchant control model
     *
     * @return MerchantControl
     */
    public function getMerchantControl()
    {
        return $this->_merchantControl;
    }

    /**
     * Returns new pack notification
     *
     * @param int|null $orderId
     * @return \FedEx\CrossBorder\Model\PackNotification
     * @throws NoSuchEntityException
     */
    public function getNewPackNotification($orderId = null)
    {
        return $this->_packNotificationFactory->create()->setOrderId(
            !empty($orderId) ? (int) $orderId: $this->getOrderId()
        );
    }

    /**
     * Returns current order id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getOrderId()
    {
        if (!isset($this->_orderId)) {
            if ($this->getCurrentPackNotificationId()) {
                $this->_orderId = $this->getCurrentPackNotification()->getOrderId();
            } else {
                $this->_orderId = (int) $this->_getRequest()->getParam('order_id');
            }
        }
        return $this->_orderId;
    }

    /**
     * Returns order link model for order
     *
     * @param Order $order
     * @return OrderLinkInterface|null
     */
    public function getOrderLink(Order $order)
    {
        return ($order instanceof Order && $order->getExtensionAttributes() ?
            $order->getExtensionAttributes()->getFdxcbData() :
            null
        );
    }

    /**
     * Returns region code by id
     *
     * @param int $regionId
     * @return string
     */
    public function getRegionCodeById($regionId)
    {
        $region = $this->_regionFactory->create();
        $region->load((int) $regionId);

        return $region->getCode();
    }

    /**
     * Retrieve request object
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->_getRequest();
    }

    /**
     * Retrieve url
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_getUrl($route, $params);
    }

    /**
     * Returns url params
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getUrlParams()
    {
        return $this->getCurrentPackNotificationId() ?
            ['pack_id' => $this->getCurrentPackNotificationId()] :
            ['order_id' => $this->getOrderId()];
    }

    /**
     * Returns weight unit
     *
     * @return string
     */
    public function getWeightUnit()
    {
        return $this->getConfig('general/locale/weight_unit');
    }

    /**
     * Sets current pack notification id
     *
     * @param int $value
     * @return $this
     */
    public function setPackNotificationId($value)
    {
        $this->_currentPackNotificationId = (int) $value;

        return $this;
    }

    /**
     * Sets order id
     *
     * @param $value
     * @return $this
     */
    public function setOrderId($value)
    {
        $this->_orderId = $value;

        return $this;
    }
}
