<?php

namespace Corra\Log\Model;

use Corra\Log\Model\ResourceModel\Log as LogResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Log
 *
 * Corra\Log\Model
 */
class Log extends AbstractModel
{
    /**
     * Initialization here.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(LogResource::class);
    }
}
