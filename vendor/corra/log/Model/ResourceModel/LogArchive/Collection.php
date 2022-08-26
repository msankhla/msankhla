<?php

namespace Corra\Log\Model\ResourceModel\LogArchive;

use Corra\Log\Model\LogArchive;
use Corra\Log\Model\ResourceModel\LogArchive as LogArchiveResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * Corra\Log\Model\ResourceModel\LogArchive
 */
class Collection extends AbstractCollection
{
    /**
     * Initialization here.
     */
    protected function _construct()
    {
        $this->_init(LogArchive::class, LogArchiveResource::class);
    }
}
