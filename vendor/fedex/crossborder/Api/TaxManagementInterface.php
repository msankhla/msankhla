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
namespace FedEx\CrossBorder\Api;

/**
 * Interface for managing tax information
 * @api
 */
interface TaxManagementInterface
{
    /**
     * Adds new item tax
     *
     * @param array $data
     * @return $this
     */
    public function addItemTax($data);

    /**
     * Clear collected tax rates
     *
     * @return $this
     */
    public function clearCollectedRates();

    /**
     * Clear quote tax
     *
     * @param int $quoteId
     * @param string|null $shippingMethod
     * @return $this
     */
    public function clearQuoteTax($quoteId, $shippingMethod = null);

    /**
     * Returns collection
     *
     * @param bool $isNew
     * @return \FedEx\CrossBorder\Model\ResourceModel\TaxRate\Collection
     */
    public function getCollection($isNew = false);

    /**
     * Collect FedEx rates
     *
     * @param int $quoteId
     * @param string $shippingMethod
     * @return $this
     */
    public function collectRates($quoteId, $shippingMethod);

    /**
     * Returns collected rates
     *
     * @param int|null $itemId
     * @return array|null
     */
    public function getCollectedRates($itemId = null);

    /**
     * Returns helper
     *
     * @return Helper
     */
    public function getHelper();

    /**
     * Returns item tax amount
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float
     */
    public function getItemTaxAmount(\Magento\Quote\Model\Quote\Item $item);

    /**
     * Returns received order total duty
     *
     * @return float
     */
    public function getOrderDuty();

    /**
     * Returns received order total tax
     *
     * @return float
     */
    public function getOrderTax();

    /**
     * Returns resource
     *
     * @return \FedEx\CrossBorder\Model\ResourceModel\TaxRate
     */
    public function getResource();

    /**
     * Returns tax rate
     *
     * @param int|null $id
     * @return \FedEx\CrossBorder\Api\Data\TaxRateInterface
     */
    public function getTaxRate($id = null);

    /**
     * Checks if tax functionality is available
     *
     * @return bool
     */
    public function isAvailable();

    /**
     * Sets received order total duty
     *
     * @param float $value
     * @return $this
     */
    public function setOrderDuty($value);

    /**
     * Sets received order total tax
     *
     * @param float $value
     * @return $this
     */
    public function setOrderTax($value);
}
