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
namespace FedEx\CrossBorder\Model\Total\Quote;

use FedEx\CrossBorder\Api\TaxManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;

class Tax extends AbstractTotal
{
    /**
     * @var TaxManagementInterface
     */
    protected $_taxManagement;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var Store
     */
    protected $_store;

    /**
     * Tax constructor.
     *
     * @param PriceCurrencyInterface $priceCurrency
     * @param TaxManagementInterface $taxManagement
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        TaxManagementInterface $taxManagement
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->_taxManagement = $taxManagement;
    }

    /**
     * Clear tax related total values in address
     *
     * @return $this
     */
    public function clearTotal()
    {
        $this->_getTotal(
        )->setTotalAmount(
            'subtotal',
            0
        )->setBaseTotalAmount(
            'subtotal',
            0
        )->setTotalAmount(
            'tax',
            0
        )->setBaseTotalAmount(
            'tax',
            0
        )->setTotalAmount(
            'discount_tax_compensation',
            0
        )->setBaseTotalAmount(
            'discount_tax_compensation',
            0
        )->setSubtotalInclTax(
            0
        )->setBaseSubtotalInclTax(
            0
        )->setAppliedTaxes(
            []
        )->setItemsAppliedTaxes(
            []
        );

        return $this;
    }

    /**
     * Collect taxes amount and prepare items prices for taxation and discount
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        if (!$this->isAvailable($shippingAssignment)) {
            return $this;
        }

        AbstractTotal::collect($quote, $shippingAssignment, $total);
        /** @var Store _store */
        $this->_store = $quote->getStore();

        $this->getTaxManagement()->clearCollectedRates();
        $this->clearTotal(
        )->processItems(
            $shippingAssignment->getItems()
        );

        return $this;
    }

    /**
     * Returns tax management
     *
     * @return TaxManagementInterface
     */
    public function getTaxManagement()
    {
        return $this->_taxManagement;
    }

    /**
     * Checks if functionality is available
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @return bool
     */
    public function isAvailable(
        ShippingAssignmentInterface $shippingAssignment
    ) {
        if (!$this->getTaxManagement()->isAvailable()) {
            return false;
        }

        // Checks if list of items not empty
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return false;
        }

        return true;
    }

    /**
     * Collect taxes for items
     *
     * @param CartItemInterface[] $items
     * @return $this
     */
    public function processItems($items)
    {
        $subtotal = $baseSubtotal = 0;
        $discountTaxCompensation = $baseDiscountTaxCompensation = 0;
        $tax = $baseTax = 0;
        $subtotalInclTax = $baseSubtotalInclTax = 0;

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren()) {
                $itemBaseTax = 0;

                foreach ($item->getChildren() as $child) {
                    $this->updateItemTaxInfo(
                        $child,
                        $item->getQty()
                    );
                    $itemBaseTax += $child->getBaseTaxAmount();
                }

                $this->updateItemTaxInfo(
                    $item,
                    1,
                    $itemBaseTax
                );
            } else {
                $this->updateItemTaxInfo($item);
            }

            $baseSubtotal += $item->getBaseRowTotal();
            $baseDiscountTaxCompensation += $item->getBaseDiscountTaxCompensation();
            $baseTax += $item->getBaseTaxAmount();
            $baseSubtotalInclTax += $item->getBaseRowTotalInclTax();

            $subtotal += $item->getRowTotal();
            $discountTaxCompensation += $item->getDiscountTaxCompensation();
            $tax += $item->getTaxAmount();
            $subtotalInclTax += $item->getRowTotalInclTax();
        }

        // Updating total values
        $this->_getTotal(
        )->setTotalAmount(
            'subtotal',
            $subtotal
        )->setBaseTotalAmount(
            'subtotal',
            $baseSubtotal
        )->setTotalAmount(
            'tax',
            $tax
        )->setBaseTotalAmount(
            'tax', $baseTax
        )->setTotalAmount(
            'discount_tax_compensation',
            $discountTaxCompensation
        )->setBaseTotalAmount(
            'discount_tax_compensation',
            $baseDiscountTaxCompensation
        )->setSubtotalInclTax(
            $subtotalInclTax
        )->setBaseSubtotalTotalInclTax(
            $baseSubtotalInclTax
        )->setBaseSubtotalInclTax(
            $baseSubtotalInclTax
        );

        // Updating address values
        $this->_getAddress(
        )->setBaseSubtotalTotalInclTax(
            $baseSubtotalInclTax
        )->setSubtotal(
            $this->_getTotal()->getSubtotal()
        )->setBaseSubtotal(
            $this->_getTotal()->getBaseSubtotal()
        );

        return $this;
    }

    /**
     * Reset item data
     *
     * @param CartItemInterface $item
     * @return $this
     */
    public function resetItemData(
        CartItemInterface $item
    ) {
        $item->setAssociatedTaxables(
            []
        )->setAppliedTaxes(
            []
        );

        return $this;
    }

    /**
     * Update item tax info
     *
     * @param CartItemInterface $item
     * @param int $parentQty
     * @param float|null $taxAmount
     * @return $this
     */
    public function updateItemTaxInfo(CartItemInterface $item, $parentQty = 1, $taxAmount = null)
    {
        $this->resetItemData($item);
        if(!isset($taxAmount)) {
            $taxAmount = $this->getTaxManagement()->getItemTaxAmount($item);
        }
        $qty = $item->getQty() * $parentQty;

        $item->setTaxPercent(
            0
        )->setBaseTaxAmount(
            $taxAmount
        )->setBasePriceInclTax(
            $this->_priceCurrency->convertAndRound($item->getPrice() + $item->getBaseTaxAmount() / $qty)
        )->setBaseRowTotalInclTax(
            $item->getBaseRowTotal() + $item->getBaseTaxAmount()
        )->setTaxAmount(
            $this->_priceCurrency->convertAndRound($item->getBaseTaxAmount())
        )->setPriceInclTax(
            $this->_priceCurrency->convertAndRound($item->getBasePriceInclTax())
        )->setRowTotalInclTax(
            $this->_priceCurrency->convertAndRound($item->getBaseRowTotalInclTax())
        );

        return $this;
    }
}