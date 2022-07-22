<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Promotions Base for Magento 2
*/
declare(strict_types=1);

namespace Amasty\Rules\Model\Rule\Action\Discount;

use Amasty\Rules\Model\Rule\ItemCalculationPrice;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;

/**
 * Amasty Rules calculation by action.
 *
 * @see \Amasty\Rules\Helper\Data::TYPE_TIERED_XN
 */
class TieredBuyxgetcheapern extends Buyxgety
{
    public const RULE_VERSION = '1.0.0';

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     *
     * @return Data Data
     *
     * @throws LocalizedException
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function calculate($rule, $item, $qty)
    {
        $this->beforeCalculate($rule);
        $discountData = $this->_calculate($rule, $item);
        $this->afterCalculate($discountData, $rule, $item);

        return $discountData;
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @return Data Data
     *
     * @throws LocalizedException
     */
    protected function _calculate($rule, $item)
    {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();
        $specialRule = $this->ruleResolver->getSpecialPromotions($rule);

        // no conditions for Y elements
        if (!$specialRule->getPromoCats() && !$specialRule->getPromoSkus()) {
            return $discountData;
        }

        $address = $item->getAddress();
        $triggerItems = $this->getTriggerElements($address, $rule);  // X elements
        $comeItemAmruleId = (int)$item->getAmrulesId();

        // find all allowed Y (discounted) elements and calculate total discount
        $passedItems = [];
        $allItems = $this->getSortedItems($address, $rule, self::DEFAULT_SORT_ORDER);
        $itemsId = $this->getItemsId($allItems);
        $discountCoefficient = $this->getDiscountCoefficient($rule, $triggerItems);
        [$itemPrice, $itemBasePrice] = $this->getItemPrices($item);
        foreach ($allItems as $sortedItem) {
            // we always skip child items and calculate discounts inside parents
            if (!$this->canProcessItem($sortedItem, $triggerItems, $passedItems)) {
                continue;
            }
            $sortedItemAmruleId = (int)$sortedItem->getAmrulesId();
            $passedItems[$sortedItemAmruleId] = $sortedItemAmruleId;

            if (!$this->isDiscountedItem($rule, $sortedItem)) {
                continue;
            }

            $qty = $this->getItemQty($sortedItem);
            if (in_array($comeItemAmruleId, $itemsId, true) && $comeItemAmruleId === $sortedItemAmruleId) {
                $discountData = $this->calculateDiscountByCoefficient(
                    $discountData,
                    $item,
                    $itemPrice,
                    $itemBasePrice,
                    $discountCoefficient,
                    $qty
                );
            }
        }

        return $discountData;
    }

    /**
     * @param Rule $rule
     * @param AbstractItem[] $triggerItems
     * @return float|int
     */
    private function getDiscountCoefficient(Rule $rule, array $triggerItems)
    {
        $step = (int)$rule->getDiscountStep();
        $items = $this->splitItemsWithQty($triggerItems);
        if ($this->itemPrice->getPriceSelector() === ItemCalculationPrice::ORIGIN_WITH_REVERT) {
            $baseSum = $this->getBaseOriginalSumOfItems($items);
        } else {
            $baseSum = $this->getBaseSumOfItems($items);
        }

        $timesToApply = floor($baseSum / max(1, $step));  // for ex. 300/100=3(times)
        $discountPercent = $timesToApply * $rule->getDiscountAmount();  // 3*5(%)=15(% of discount for whole cart)

        return $discountPercent / self::HUNDRED_PERCENT;  // 15(%)/100(%)=0.15(coefficient for item)
    }

    /**
     * @param AbstractItem $item
     * @return array
     */
    private function getItemPrices(AbstractItem $item): array
    {
        if ($this->itemPrice->getPriceSelector() === ItemCalculationPrice::ORIGIN_WITH_REVERT) {
            $itemPrice = $this->itemPrice->getItemOriginalPrice($item);
            $itemBasePrice = $this->itemPrice->getItemBaseOriginalPrice($item);
        } else {
            $itemPrice = $this->itemPrice->getItemPrice($item);
            $itemBasePrice = $this->itemPrice->getItemBasePrice($item);
        }

        return [$itemPrice, $itemBasePrice];
    }
}
