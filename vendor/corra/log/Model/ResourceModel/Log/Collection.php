<?php

namespace Corra\Log\Model\ResourceModel\Log;

use Corra\Log\Model\Log;
use Corra\Log\Model\ResourceModel\Log as LogResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * Corra\Log\Model\ResourceModel\Log
 */
class Collection extends AbstractCollection
{
    /**
     * Initialization here.
     */
    protected function _construct()
    {
        $this->_init(Log::class, LogResource::class);
    }
}
