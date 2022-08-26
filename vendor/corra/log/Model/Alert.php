<?php

namespace Corra\Log\Model;

use Corra\Log\Model\ResourceModel\Alert as AlertResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Alert
 *
 * Corra\Log\Model
 */
class Alert extends AbstractModel
{
    /**
     * Initialization here.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(AlertResource::class);
    }
}
