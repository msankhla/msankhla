<?php
/**
 * @package     BlueAcorn/Core
 * @version     1.0.0
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
namespace BlueAcorn\Core\Model;

use BlueAcorn\Core\Model\ResourceModel\ContentVersion as ContentVersionResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * Class ContentVersion
 * @package BlueAcorn\Core\Model
 */
class ContentVersion extends AbstractModel
{
    /**
     * Initialize corresponding resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ContentVersionResourceModel::class);
        $this->_collectionName = ContentVersionResourceModel\Collection::class;
    }
}