<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Promotions Base for Magento 2
*/
declare(strict_types=1);

namespace Amasty\Rules\Plugin;

use Amasty\Rules\Helper\Discount;
use Magento\Quote\Model\Quote;

class ResetMaxDiscountCache
{
    /**
     * @param Quote $subject
     * @return array
     */
    public function beforeCollectTotals(Quote $subject): array
    {
        Discount::$maxDiscount = [];
        
        return [];
    }
}
