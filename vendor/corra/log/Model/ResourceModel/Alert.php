<?php

namespace Corra\Log\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Alert
 *
 * Corra\Log\Model\ResourceModel
 */
class Alert extends AbstractDb
{
    /**
     * Initialization here.
     */
    protected function _construct()
    {
        $this->_init('corra_log_alert', 'id');
    }
}
