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
namespace FedEx\CrossBorder\Model\ResourceModel\AvailableCountries;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use FedEx\CrossBorder\Model\AvailableCountries as Model;
use FedEx\CrossBorder\Model\ResourceModel\AvailableCountries as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'fedex_crossborder_availablecountries_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'availablecountries_collection';

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