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

use FedEx\CrossBorder\Api\Data\TaxRateInterface;
use FedEx\CrossBorder\Model\ResourceModel\TaxRate as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class TaxRate extends AbstractModel implements TaxRateInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Returns quote id
     *
     * @return int
     */
    public function getQuoteId()
    {
        return $this->getData(static::QUOTE_ID);
    }

    /**
     * Sets quote id
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(
            static::QUOTE_ID,
            (int) $quoteId
        );
    }

    /**
     * Returns item id
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->getData(static::ITEM_ID);
    }

    /**
     * Sets item id
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId)
    {
        return $this->setData(
            static::ITEM_ID,
            (int) $itemId
        );
    }

    /**
     * Returns shipping method
     *
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->getData(static::SHIPPING_METHOD);
    }

    /**
     * Sets shipping method
     *
     * @param string $shippingMethod
     * @return $this
     */
    public function setShippingMethod($shippingMethod)
    {
        return $this->setData(
            static::SHIPPING_METHOD,
            $shippingMethod
        );
    }

    /**
     * Returns tax amount
     *
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->getData(static::TAX_AMOUNT);
    }

    /**
     * Sets tax amount
     *
     * @param float $amount
     * @return $this
     */
    public function setTaxAmount($amount)
    {
        return $this->setData(
            static::TAX_AMOUNT,
            (float) $amount
        );
    }

    /**
     * Returns duty amount
     *
     * @return float
     */
    public function getDutyAmount()
    {
        return $this->getData(static::DUTY_AMOUNT);
    }

    /**
     * Sets duty amount
     *
     * @param float $amount
     * @return $this
     */
    public function setDutyAmount($amount)
    {
        return $this->setData(
            static::DUTY_AMOUNT,
            (float) $amount
        );
    }

    /**
     * Load item data
     *
     * @param int $itemId
     * @param string $shippingMethod
     * @return $this
     */
    public function loadItemData(
        $itemId,
        $shippingMethod
    ) {
        $this->getResource()->loadItemData($this, $itemId, $shippingMethod);

        return $this;
    }
}