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

use FedEx\CrossBorder\Api\Data\OrderLinkInterface;
use FedEx\CrossBorder\Api\Data\OrderLink\AddressInterface;
use FedEx\CrossBorder\Model\OrderLink\AddressFactory;
use FedEx\CrossBorder\Model\ResourceModel\OrderLink as ResourceModel;
use FedEx\CrossBorder\Ui\Component\Order\Listing\Column\Status\Options as Status;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

class OrderLink extends AbstractModel implements OrderLinkInterface
{
    /**
     * @var AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var AddressInterface
     */
    protected $_originalShippingAddress;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var Status
     */
    protected $_status;

    /**
     * OrderLink constructor.
     *
     * @param AddressFactory $addressFactory
     * @param OrderFactory $orderFactory
     * @param Status $status
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        AddressFactory $addressFactory,
        OrderFactory $orderFactory,
        Status $status,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_addressFactory = $addressFactory;
        $this->_orderFactory = $orderFactory;
        $this->_status = $status;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
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
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setData(static::ORDER_ID, $orderId);
    }

    /**
     * Returns FedEx order number
     *
     * @return string
     */
    public function getFxcbOrderNumber()
    {
        return $this->getData(static::FXCB_ORDER_NUMBER);
    }

    /**
     * Sets FedEx order number
     *
     * @param string $fxcbOrderNumber
     * @return $this
     */
    public function setFxcbOrderNumber($fxcbOrderNumber)
    {
        return $this->setData(static::FXCB_ORDER_NUMBER, $fxcbOrderNumber);
    }

    /**
     * Returns FedEx tracking link
     *
     * @return string
     */
    public function getTrackingLink()
    {
        return $this->getData(static::TRACKING_LINK);
    }

    /**
     * Sets FedEx tracking link
     *
     * @param string $link
     * @return $this
     */
    public function setTrackingLink($link)
    {
        return $this->setData(static::TRACKING_LINK, $link);
    }

    /**
     * Returns FedEx status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(static::STATUS);
    }

    /**
     * Sets FedEx status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(static::STATUS, $status);
    }

    /**
     * Returns status name
     *
     * @return string
     */
    public function getStatusName()
    {
        return $this->_status->getLabel(
            $this->getStatus()
        );
    }

    /**
     * Returns order
     *
     * @param bool $isNew
     * @return Order
     */
    public function getOrder($isNew = false)
    {
        if (!isset($this->_order) || $isNew) {
            $this->_order = $this->_orderFactory->create()->load($this->getOrderId());
        }

        return $this->_order;
    }

    /**
     * Returns original shipping address
     *
     * @param bool $createEmpty
     * @return AddressInterface|null
     */
    public function getOriginalShippingAddress($createEmpty = false)
    {
        if (!isset($this->_originalShippingAddress)) {
            $this->_originalShippingAddress = $this->_addressFactory->create();
            $this->_originalShippingAddress->load(
                $this->getId(),
                AddressInterface::ORDER_LINK_ID
            );

            if (!$this->_originalShippingAddress->getId() && !$createEmpty) {
                $this->_originalShippingAddress = null;
            }
        }

        if ($this->_originalShippingAddress && $this->_originalShippingAddress->getOrderLinkId() != $this->getId()) {
            $this->_originalShippingAddress->setOrderLinkId(
                $this->getId()
            );
        }

        return $this->_originalShippingAddress;
    }

    /**
     * Checks if the original shipping address is defined
     *
     * @return bool
     */
    public function hasOriginalShippingAddress()
    {
        return isset($this->_originalShippingAddress);
    }

    /**
     * Sets original shipping address
     *
     * @param AddressInterface|array $value
     * @return $this
     */
    public function setOriginalShippingAddress($value = null)
    {
        if (is_array($value)) {
            if (isset($value[AddressInterface::ORDER_LINK_ID])) {
                unset($value[AddressInterface::ORDER_LINK_ID]);
            }
            $this->getOriginalShippingAddress(true)->addData($value);
        } elseif ($value instanceof Address) {
            if ($value->hasData(AddressInterface::ORDER_LINK_ID)) {
                $value->unsetData(AddressInterface::ORDER_LINK_ID);
            }
            $this->getOriginalShippingAddress(true)->addData(
                $value->getData()
            );
        }

        return $this;
    }
}