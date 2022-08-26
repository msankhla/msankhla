<?php

namespace Corra\Log\Model;

use Corra\Log\Model\ResourceModel\LogArchive as LogArchiveResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class LogArchive
 *
 * Corra\Log\Model
 */
class LogArchive extends AbstractModel
{
    /**
     * Initialization here.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(LogArchiveResource::class);
    }
}
