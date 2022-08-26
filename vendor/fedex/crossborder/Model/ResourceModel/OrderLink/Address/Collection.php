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
namespace FedEx\CrossBorder\Model\ResourceModel\OrderLink\Address;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use FedEx\CrossBorder\Model\OrderLink\Address as Model;
use FedEx\CrossBorder\Model\ResourceModel\OrderLink\Address as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'fdxcb_order_link_address_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'fdxcb_order_link_address';

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