<?php

namespace Corra\Log\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Log
 *
 * Corra\Log\Model\ResourceModel
 */
class Log extends AbstractDb
{
    /**
     * Initialization here.
     */
    protected function _construct()
    {
        $this->_init('corra_log', 'id');
    }
}
