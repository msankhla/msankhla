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
namespace FedEx\CrossBorder\Model\OrderManagement;

use FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformationInterface;
use FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformation\AddressInformationInterface;
use FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformation\ProductInformationInterface;
use Magento\Framework\DataObject;

class OrderInformation extends DataObject implements OrderInformationInterface
{
    /**
     * Returns order id
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->getData(static::ORDER_ID);
    }

    /**
     * Sets order id
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setOrderId($value)
    {
        return $this->setData(static::ORDER_ID, $value);
    }
    /**
     * Returns order status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(static::STATUS);
    }

    /**
     * Sets order status
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setStatus($value)
    {
        return $this->setData(static::STATUS, $value);
    }

    /**
     * Returns currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->getData(static::CURRENCY);
    }

    /**
     * Sets currency
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setCurrency($value)
    {
        return $this->setData(static::CURRENCY, $value);
    }

    /**
     * Returns currency rate
     *
     * @return float
     */
    public function getCurrencyRate()
    {
        return (float) $this->getData(static::CURRENCY_RATE);
    }

    /**
     * Sets currency rate
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setCurrencyRate($value)
    {
        return $this->setData(static::CURRENCY_RATE, (float) $value);
    }

    /**
     * Returns tracking link
     *
     * @return string
     */
    public function getTrackingLink()
    {
        return $this->getData(static::TRACKING_LINK);
    }

    /**
     * Sets tracking link
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setTrackingLink($value)
    {
        return $this->setData(static::TRACKING_LINK, $value);
    }

    /**
     * Returns customer email
     *
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->getData(static::CUSTOMER_EMAIL);
    }

    /**
     * Sets customer email
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setCustomerEmail($value)
    {
        return $this->setData(static::CUSTOMER_EMAIL, $value);
    }

    /**
     * Returns products
     *
     * @return ProductInformationInterface[]
     */
    public function getProducts()
    {
        return $this->getData(static::PRODUCTS);
    }

    /**
     * Sets products
     *
     * @param ProductInformationInterface[] $value
     * @return OrderInformationInterface
     */
    public function setProducts($value)
    {
        return $this->setData(static::PRODUCTS, $value);
    }

    /**
     * Returns shipping address
     *
     * @return AddressInformationInterface
     */
    public function getShippingAddress()
    {
        return $this->getData(static::SHIPPING_ADDRESS);
    }

    /**
     * Sets shipping address
     *
     * @param AddressInformationInterface $value
     * @return OrderInformationInterface
     */
    public function setShippingAddress($value)
    {
        return $this->setData(static::SHIPPING_ADDRESS, $value);
    }

    /**
     * Returns billing address
     *
     * @return AddressInformationInterface
     */
    public function getBillingAddress()
    {
        return $this->getData(static::BILLING_ADDRESS);
    }

    /**
     * Sets billing address
     *
     * @param AddressInformationInterface $value
     * @return OrderInformationInterface
     */
    public function setBillingAddress($value)
    {
        return $this->setData(static::BILLING_ADDRESS, $value);
    }

    /**
     * Returns shipping service id
     *
     * @return int
     */
    public function getShippingServiceId()
    {
        return $this->getData(static::SHIPPING_SERVICE_ID);
    }

    /**
     * Sets shipping service id
     *
     * @param int $value
     * @return OrderInformationInterface
     */
    public function setShippingServiceId($value)
    {
        return $this->setData(static::SHIPPING_SERVICE_ID, $value);
    }

    /**
     * Returns shipping cost
     *
     * @return float
     */
    public function getShippingCost()
    {
        return (float) $this->getData(static::SHIPPING_COST);
    }

    /**
     * Sets shipping domestic cost
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setShippingCost($value)
    {
        return $this->setData(static::SHIPPING_COST, (float) $value);
    }

    /**
     * Returns shipping cost
     *
     * @return float
     */
    public function getShippingDomesticCost()
    {
        return (float) $this->getData(static::SHIPPING_DOMESTIC_COST);
    }

    /**
     * Sets shipping domestic cost
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setShippingDomesticCost($value)
    {
        return $this->setData(static::SHIPPING_DOMESTIC_COST, (float) $value);
    }

    /**
     * Returns duty cost
     *
     * @return float
     */
    public function getDutyCost()
    {
        return (float) $this->getData(static::DUTY_COST);
    }

    /**
     * Sets duty cost
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setDutyCost($value)
    {
        return $this->setData(static::DUTY_COST, (float) $value);
    }

    /**
     * Returns insurance cost
     *
     * @return float
     */
    public function getInsuranceCost()
    {
        return (float) $this->getData(static::INSURANCE_COST);
    }

    /**
     * Sets insurance cost
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setInsuranceCost($value)
    {
        return $this->setData(static::INSURANCE_COST, (float) $value);
    }

    /**
     * Returns tax cost
     *
     * @return float
     */
    public function getTaxCost()
    {
        return (float) $this->getData(static::TAX_COST);
    }

    /**
     * Sets tax cost
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setTaxCost($value)
    {
        return $this->setData(static::TAX_COST, (float) $value);
    }

    /**
     * Returns payment type
     *
     * @return string
     */
    public function getPaymentType()
    {
        return $this->getData(static::PAYMENT_TYPE);
    }

    /**
     * Sets payment type
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setPaymentType($value)
    {
        return $this->setData(static::PAYMENT_TYPE, $value);
    }

    /**
     * Returns sub total
     *
     * @return float
     */
    public function getSubTotal()
    {
        return (float) $this->getData(static::SUB_TOTAL);
    }

    /**
     * Sets sub total
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setSubTotal($value)
    {
        return $this->setData(static::SUB_TOTAL, (float) $value);
    }

    /**
     * Returns total
     *
     * @return float
     */
    public function getTotal()
    {
        return (float) $this->getData(static::TOTAL);
    }

    /**
     * Sets total
     *
     * @param float $value
     * @return OrderInformationInterface
     */
    public function setTotal($value)
    {
        return $this->setData(static::TOTAL, (float) $value);
    }

    /**
     * Returns comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->getData(static::COMMENT);
    }

    /**
     * Sets comment
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setComment($value)
    {
        return $this->setData(static::COMMENT, $value);
    }

    /**
     * Returns custom attributes
     *
     * @return string[]
     */
    public function getCustomAttributes()
    {
        return $this->getData(static::CUSTOM_ATTRIBUTES);
    }

    /**
     * Sets custom attributes
     * @param string[] $value
     * @return OrderInformationInterface
     */
    public function setCustomAttributes($value)
    {
        return $this->setData(static::CUSTOM_ATTRIBUTES, $value);
    }
}
