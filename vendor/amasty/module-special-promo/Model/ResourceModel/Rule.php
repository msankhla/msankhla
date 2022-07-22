<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Promotions Base for Magento 2
*/
declare(strict_types=1);

namespace Amasty\Rules\Model\ResourceModel;

/**
 * Resource model for Rule object.
 */
class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'amasty_amrules_rule';

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }

    public function disableSpecialPromoRules(): void
    {
        $mainTable = $this->getMainTable();
        $salesRuleTable = $this->getTable('salesrule');
        $select = $this->getConnection()->select()
            ->from($mainTable, ['salesrule_id'])
            ->join([$salesRuleTable], $mainTable . '.salesrule_id = ' . $salesRuleTable . '.rule_id', ['is_active'])
            ->where('is_active = ?', 1)
            ->where('simple_action NOT IN (?)', ['by_fixed', 'by_percent', 'cart_fixed']);

        $ruleIds = $this->getConnection()->fetchCol($select);
        if (!empty($ruleIds)) {
            $this->getConnection()->update($salesRuleTable, ['is_active' => 0], ['rule_id IN (?)' => $ruleIds]);
        }
    }
}
