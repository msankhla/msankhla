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

interface TaxRateInterface
{
    const QUOTE_ID          = 'quote_id';
    const ITEM_ID           = 'item_id';
    const SHIPPING_METHOD   = 'shipping_method';
    const TAX_AMOUNT        = 'tax_amount';
    const DUTY_AMOUNT       = 'duty_amount';

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
     * Returns quote id
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Sets quote id
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * Returns item id
     *
     * @return int
     */
    public function getItemId();

    /**
     * Sets item id
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * Returns shipping method
     *
     * @return string
     */
    public function getShippingMethod();

    /**
     * Sets shipping method
     *
     * @param string $shippingMethod
     * @return $this
     */
    public function setShippingMethod($shippingMethod);

    /**
     * Returns tax amount
     *
     * @return float
     */
    public function getTaxAmount();

    /**
     * Sets tax amount
     *
     * @param float $amount
     * @return $this
     */
    public function setTaxAmount($amount);

    /**
     * Returns duty amount
     *
     * @return float
     */
    public function getDutyAmount();

    /**
     * Sets duty amount
     *
     * @param float $amount
     * @return $this
     */
    public function setDutyAmount($amount);

    /**
     * Load item data
     *
     * @param int $itemId
     * @param string $shippingMethod
     * @return $this
     */
    public function loadItemData($itemId, $shippingMethod);
}
