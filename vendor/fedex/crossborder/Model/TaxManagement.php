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
use FedEx\CrossBorder\Api\Data\TaxRateInterfaceFactory;
use FedEx\CrossBorder\Api\TaxManagementInterface;
use FedEx\CrossBorder\Helper\Data as Helper;
use FedEx\CrossBorder\Model\ResourceModel\TaxRate as ResourceModel;
use FedEx\CrossBorder\Model\ResourceModel\TaxRate\Collection;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class TaxManagement implements TaxManagementInterface
{
    /**
     * @var array
     */
    protected $_collectedRates;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var float
     */
    protected $_orderDuty;

    /**
     * @var float
     */
    protected $_orderTax;

    /**
     * @var ResourceModel
     */
    protected $_resourceModel;

    /**
     * @var TaxRateInterfaceFactory
     */
    protected $_taxRateFactory;

    /**
     * TaxManagement constructor.
     *
     * @param Helper $helper
     * @param ResourceModel $resourceModel
     * @param TaxRateInterfaceFactory $taxRateFactory
     */
    public function __construct(
        Helper $helper,
        ResourceModel $resourceModel,
        TaxRateInterfaceFactory $taxRateFactory
    ) {
        $this->_helper = $helper;
        $this->_resourceModel = $resourceModel;
        $this->_taxRateFactory = $taxRateFactory;
    }

    /**
     * Adds new item tax
     *
     * @param array $data
     * @return $this
     */
    public function addItemTax($data)
    {
        $this->getResource()->addItemTax($data);

        return $this;
    }

    /**
     * Clear collected tax rates
     *
     * @return $this
     */
    public function clearCollectedRates()
    {
        $this->_collectedRates = null;

        return $this;
    }

    /**
     * Clear quote tax
     *
     * @param int $quoteId
     * @param string|null $shippingMethod
     * @return $this
     */
    public function clearQuoteTax($quoteId, $shippingMethod = null)
    {
        $this->getResource()->clearByQuote($quoteId, $shippingMethod);

        return $this;
    }

    /**
     * Returns collection
     *
     * @param bool $isNew
     * @return Collection
     */
    public function getCollection($isNew = false)
    {
        return $this->getResource()->getCollection($isNew);
    }

    /**
     * Collect FedEx rates
     *
     * @param int $quoteId
     * @param string $shippingMethod
     * @return $this
     */
    public function collectRates($quoteId, $shippingMethod)
    {
        $this->getCollection(
            true
        )->addFieldToFilter(
            'main_table.' . TaxRateInterface::QUOTE_ID,
            (int) $quoteId
        )->addFieldToFilter(
            'main_table.' . TaxRateInterface::SHIPPING_METHOD,
            $shippingMethod
        )->getSelect(
        )->joinLeft(
            ['qi' => 'quote_item'],
            'main_table.item_id = qi.item_id',
            'qty'
        )->joinLeft(
            ['pqi' => 'quote_item'],
            'qi.parent_item_id = pqi.item_id',
            []
        );
        $this->getCollection()->addExpressionFieldToSelect(
            'cost_amount',
            'IF(pqi.product_type = "configurable", pqi.base_row_total - pqi.base_discount_amount, qi.base_row_total - qi.base_discount_amount)',
            []
        );

        $taxSum     = 0;
        $costSum    = 0;
        $dutySum    = 0;
        $qtySum     = 0;
        $tax        = $this->getOrderTax();
        $duty       = $this->getOrderDuty();

        /** @var TaxRateInterface $item */
        foreach ($this->getCollection() as $item) {
            $dutySum    += $item->getDutyAmount();
            $taxSum     += $item->getTaxAmount();
            $costSum    += $item->getCostAmount();
            $qtySum     += $item->getQty();
        }

        foreach ($this->getCollection() as $item) {
            $itemId = $item->getItemId();
            $this->_collectedRates[$itemId] = [
                'dutyAmount'    => $item->getDutyAmount(),
                'taxAmount'     => $item->getTaxAmount(),
            ];

            if (isset($tax)) {
                if ($taxSum > 0) {
                    $this->_collectedRates[$itemId]['taxAmount'] = round($item->getTaxAmount() * $tax / $taxSum, 2);
                    $taxSum -= $item->getTaxAmount();
                } elseif ($costSum > 0) {
                    $this->_collectedRates[$itemId]['taxAmount'] = round($item->getCostAmount() * $tax / $costSum, 2);
                } else {
                    $this->_collectedRates[$itemId]['taxAmount'] = round($item->getQty() * $tax / $qtySum, 2);
                }

                $tax -= $this->_collectedRates[$itemId]['taxAmount'];
            }

            if (isset($duty)) {
                if ($dutySum > 0) {
                    $this->_collectedRates[$itemId]['dutyAmount'] = round($item->getDutyAmount() * $duty / $dutySum, 2);
                    $dutySum -= $item->getDutyAmount();
                } elseif ($costSum > 0) {
                    $this->_collectedRates[$itemId]['dutyAmount'] = round($item->getCostAmount() * $duty / $costSum, 2);
                } else {
                    $this->_collectedRates[$itemId]['dutyAmount'] = round($item->getQty() * $duty / $qtySum, 2);
                }

                $duty -= $this->_collectedRates[$itemId]['dutyAmount'];
            }

            if (isset($duty) || isset($tax)) {
                $costSum -= $item->getCostAmount();
                $qtySum -= $item->getQty();
            }
        }

        return $this;
    }

    /**
     * Returns collected rates
     *
     * @param int|null $itemId
     * @return array|null
     */
    public function getCollectedRates($itemId = null)
    {
        if (!isset($itemId)) {
            return $this->_collectedRates;
        }

        return (isset($this->_collectedRates[$itemId]) ? $this->_collectedRates[$itemId] : null);
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
     * Returns item tax amount
     *
     * @param QuoteItem $item
     * @return float
     */
    public function getItemTaxAmount(QuoteItem $item)
    {
        $result = 0;
        if ($item && $item->getId()) {
            if (!isset($this->_collectedRates)) {
                $this->collectRates(
                    $item->getQuoteId(),
                    $item->getAddress()->getShippingMethod()
                );
            }

            $rates = $this->getCollectedRates(
                $item->getId()
            );

            if ($rates) {
                $result = $rates['taxAmount'] + $rates['dutyAmount'];
            }
        }

        return (float) $result;
    }

    /**
     * Returns received order total duty
     *
     * @return float
     */
    public function getOrderDuty()
    {
        return $this->_orderDuty;
    }

    /**
     * Returns received order total tax
     *
     * @return float
     */
    public function getOrderTax()
    {
        return $this->_orderTax;
    }

    /**
     * Returns resource
     *
     * @return ResourceModel
     */
    public function getResource()
    {
        return $this->_resourceModel;
    }

    /**
     * Returns tax rate
     *
     * @param int|null $id
     * @return TaxRateInterface
     */
    public function getTaxRate($id = null)
    {
        $taxRate = $this->_taxRateFactory->create();
        if (!empty($id)) {
            $taxRate->load($id);
        }

        return $taxRate;
    }

    /**
     * Checks if tax functionality is available
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->getHelper()->isInternational();
    }

    /**
     * Sets received order total duty
     *
     * @param float $value
     * @return $this
     */
    public function setOrderDuty($value)
    {
        $this->_orderDuty = (float) $value;

        return $this;
    }

    /**
     * Sets received order total tax
     *
     * @param float $value
     * @return $this
     */
    public function setOrderTax($value)
    {
        $this->_orderTax = (float) $value;

        return $this;
    }
}