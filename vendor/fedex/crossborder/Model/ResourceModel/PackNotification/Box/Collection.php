<?php
/**
 * FedEx Cross Border component
 *
 * @category    FedEx
 * @package     FedEx_CrossBorder
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use FedEx\CrossBorder\Model\PackNotification\Box as Model;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'fedex_crossborder_pack_notification_box_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'pack_notification_box';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Model::class,
            ResourceModel::class
        );
    }
}