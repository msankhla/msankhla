<?php
/**
 * @package     BlueAcorn/Core
 * @version     1.0.0
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
namespace BlueAcorn\Core\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ContentVersion
 * @package BlueAcorn\Core\Model\ResourceModel
 */
class ContentVersion extends AbstractDb
{
    /**
     * Defines main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('blueacorn_content_version', 'content_id');
    }
}