<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Promotions Base for Magento 2
*/

namespace Amasty\Rules\Model\Rule\Action\Discount;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;

/**
 * Amasty Rules calculation by action.
 *
 * @see \Amasty\Rules\Helper\Data::TYPE_AMOUNT
 */
class Moneyamount extends AbstractRule
{
    public const RULE_VERSION = '1.0.0';
    public const DEFAULT_SORT_ORDER = 'asc';

    /**
     * @var int
     */
    protected static $itemsCounter = 0;

    /**
     * @var int
     */
    protected static $discountAmount = 0;

    /**
     * @var int
     */
    protected $baseDiscountAmount = 0;

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     *
     * @return Data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
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
     *
     * @return Data Data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _calculate($rule, $item)
    {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();
        $allItems = $this->getSortedItems($item->getAddress(), $rule, self::DEFAULT_SORT_ORDER);
        $step = (int)$rule->getDiscountStep();
        $baseSum = $this->getBaseSumOfItems($allItems);

        $timesToApply = floor($baseSum / max(1, $step));
        $maxTimesToApply = max(0, (int)$rule->getDiscountQty()); // remove negative values if any

        if ($maxTimesToApply) {
            $timesToApply = min($timesToApply, $maxTimesToApply);
        }

        $discountAmount = $timesToApply * $rule->getDiscountAmount();
        $this->baseDiscountAmount = $discountAmount;

        if ($discountAmount <= 0.001) {
            return $discountData;
        }

        $discountCoefficient = $discountAmount / $baseSum; // for ex. 4/50=0.08(coefficient for item)

        $itemsId = $this->getItemsId($allItems);
        if (in_array((int)$item->getAmrulesId(), $itemsId, true)) {
            $itemQty = $this->getArrayValueCount($itemsId, $item->getAmrulesId());
            $itemPrice = $this->validator->getItemPrice($item);
            $itemBasePrice = $this->validator->getItemBasePrice($item);
            $discountData = $this->calculateDiscountByCoefficient(
                $discountData,
                $item,
                $itemPrice,
                $itemBasePrice,
                $discountCoefficient,
                $itemQty
            );
        }
        
        $this->resolveDiscountAmount($item, $discountData);

        return $discountData;
    }
    
    /**
     * @param AbstractItem $item
     * @param Data $discountData
     */
    protected function resolveDiscountAmount(AbstractItem $item, Data $discountData): void
    {
        self::$itemsCounter++;
        $allItems = $this->getAllItems($item->getAddress());
        self::$discountAmount += round($discountData->getAmount(), 2);

        if (self::$itemsCounter !== count($allItems)) {
            return;
        }

        $diff = round($this->baseDiscountAmount - self::$discountAmount, 2);
        self::$itemsCounter = 0;
        self::$discountAmount = 0;

        if ($diff !== 0.00) {
            $discountData->setAmount($discountData->getAmount() + $diff);
            $discountData->setBaseAmount($discountData->getBaseAmount() + $diff);
        }
    }
}
