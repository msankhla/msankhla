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
namespace FedEx\CrossBorder\Api\Data\OrderManagement;

interface OrderInformationInterface
{
    const ORDER_ID                  = 'order_id';
    const STATUS                    = 'status';
    const CURRENCY                  = 'currency';
    const CURRENCY_RATE             = 'currency_rate';
    const TRACKING_LINK             = 'tracking_link';
    const CUSTOMER_EMAIL            = 'customer_email';
    const PRODUCTS                  = 'products';
    const SHIPPING_ADDRESS          = 'shipping_address';
    const BILLING_ADDRESS           = 'billing_address';
    const SHIPPING_SERVICE_ID       = 'shipping_service_id';
    const SHIPPING_COST             = 'shipping_cost';
    const SHIPPING_DOMESTIC_COST    = 'shipping_domestic_cost';
    const DUTY_COST                 = 'duty_cost';
    const INSURANCE_COST            = 'insurance_cost';
    const TAX_COST                  = 'tax_cost';
    const PAYMENT_TYPE              = 'payment_type';
    const SUB_TOTAL                 = 'sub_total';
    const TOTAL                     = 'total';
    const CUSTOM_ATTRIBUTES         = 'custom_attributes';
    const COMMENT                   = 'comment';

    /**
     * Returns order id
     *
     * @return string
     */
    public function getOrderId();

    /**
     * Sets order id
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setOrderId($value);

    /**
     * Returns order status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Sets order status
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setStatus($value);

    /**
     * Returns currency
     *
     * @return string
     */
    public function getCurrency();

    /**
     * Sets currency
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setCurrency($value);

    /**
     * Returns currency rate
     *
     * @return float
     */
    public function getCurrencyRate();

    /**
     * Sets currency rate
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setCurrencyRate($value);

    /**
     * Returns tracking link
     *
     * @return string
     */
    public function getTrackingLink();

    /**
     * Sets tracking link
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setTrackingLink($value);

    /**
     * Returns customer email
     *
     * @return string
     */
    public function getCustomerEmail();

    /**
     * Sets customer email
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setCustomerEmail($value);

    /**
     * Returns products
     *
     * @return \FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformation\ProductInformationInterface[]
     */
    public function getProducts();

    /**
     * Sets products
     *
     * @param \FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformation\ProductInformationInterface[] $value
     * @return OrderInformationInterface
     */
    public function setProducts($value);

    /**
     * Returns shipping address
     *
     * @return \FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformation\AddressInformationInterface
     */
    public function getShippingAddress();

    /**
     * Sets shipping address
     *
     * @param \FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformation\AddressInformationInterface $value
     * @return OrderInformationInterface
     */
    public function setShippingAddress($value);

    /**
     * Returns billing address
     *
     * @return \FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformation\AddressInformationInterface
     */
    public function getBillingAddress();

    /**
     * Sets billing address
     *
     * @param \FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformation\AddressInformationInterface $value
     * @return OrderInformationInterface
     */
    public function setBillingAddress($value);

    /**
     * Returns shipping service id
     *
     * @return int
     */
    public function getShippingServiceId();

    /**
     * Sets shipping service id
     *
     * @param int $value
     * @return OrderInformationInterface
     */
    public function setShippingServiceId($value);

    /**
     * Returns shipping cost
     *
     * @return float
     */
    public function getShippingCost();

    /**
     * Sets shipping domestic cost
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setShippingCost($value);

    /**
     * Returns shipping cost
     *
     * @return float
     */
    public function getShippingDomesticCost();

    /**
     * Sets shipping domestic cost
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setShippingDomesticCost($value);

    /**
     * Returns duty cost
     *
     * @return float
     */
    public function getDutyCost();

    /**
     * Sets duty cost
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setDutyCost($value);

    /**
     * Returns insurance cost
     *
     * @return float
     */
    public function getInsuranceCost();

    /**
     * Sets insurance cost
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setInsuranceCost($value);

    /**
     * Returns tax cost
     *
     * @return float
     */
    public function getTaxCost();

    /**
     * Sets tax cost
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setTaxCost($value);

    /**
     * Returns payment type
     *
     * @return string
     */
    public function getPaymentType();

    /**
     * Sets payment type
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setPaymentType($value);

    /**
     * Returns sub total
     *
     * @return float
     */
    public function getSubTotal();

    /**
     * Sets sub total
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setSubTotal($value);

    /**
     * Returns total
     *
     * @return float
     */
    public function getTotal();

    /**
     * Sets total
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setTotal($value);

    /**
     * Returns custom attributes
     *
     * @return string[]
     */
    public function getCustomAttributes();

    /**
     * Returns custom attributes
     *
     * @param string[] $value
     * @return OrderInformationInterface
     */
    public function setCustomAttributes($value);

    /**
     * Returns comment
     *
     * @return string
     */
    public function getComment();

    /**
     * Sets comment
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setComment($value);
}