<?php

namespace Corra\Log\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class LogArchive
 *
 * Corra\Log\Model\ResourceModel
 */
class LogArchive extends AbstractDb
{
    /**
     * Initialization here.
     */
    protected function _construct()
    {
        $this->_init('corra_log_archive', 'id');
    }
}
