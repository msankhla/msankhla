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
namespace FedEx\CrossBorder\Api\Data;

interface OrderLinkInterface
{
    const ORDER_ID          = 'order_id';
    const FXCB_ORDER_NUMBER = 'fxcb_order_number';
    const TRACKING_LINK     = 'tracking_link';
    const STATUS            = 'fxcb_status';

    /**
     * Returns id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Sets id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Returns order id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Sets order id
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Returns FedEx order number
     *
     * @return string
     */
    public function getFxcbOrderNumber();

    /**
     * Sets FedEx order number
     *
     * @param string $fxcbOrderNumber
     * @return $this
     */
    public function setFxcbOrderNumber($fxcbOrderNumber);

    /**
     * Returns FedEx tracking link
     *
     * @return string
     */
    public function getTrackingLink();

    /**
     * Sets FedEx tracking link
     *
     * @param string $link
     * @return $this
     */
    public function setTrackingLink($link);

    /**
     * Returns FedEx status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Sets FedEx status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Returns original shipping address
     *
     * @param bool $createEmpty
     * @return \FedEx\CrossBorder\Api\Data\OrderLink\AddressInterface|null
     */
    public function getOriginalShippingAddress($createEmpty = false);

    /**
     * Sets original shipping address
     *
     * @param \FedEx\CrossBorder\Api\Data\OrderLink\AddressInterface|array $value
     * @return $this
     */
    public function setOriginalShippingAddress($value);
}
