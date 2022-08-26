<?php
/**
 * @package     BlueAcorn/Core
 * @version     1.0.0
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
namespace BlueAcorn\Core\Model\ResourceModel\ContentVersion;

use BlueAcorn\Core\Model\ContentVersion as ContentVersionModel;
use BlueAcorn\Core\Model\ResourceModel\ContentVersion as ContentVersionResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package BlueAcorn\Core\Model\ResourceModel\ContentVersion
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ContentVersionModel::class, ContentVersionResourceModel::class);
    }
}