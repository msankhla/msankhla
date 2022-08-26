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
namespace FedEx\CrossBorder\Ui\DataProvider\Scheduler;

use FedEx\CrossBorder\Api\Data\SchedulerInterface;
use FedEx\CrossBorder\Model\ResourceModel\Scheduler\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Form extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $_loadedData;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * SchedulerForm constructor.
     *
     * @param Registry $registry
     * @param CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->collection = $collectionFactory->create()->addFieldToFilter(
            'entity_id',
            $this->getCurrentScheduler()->getId()
        );
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Returns current scheduler
     *
     * @return SchedulerInterface
     */
    public function getCurrentScheduler()
    {
        return $this->_registry->registry('current_scheduler');
    }

    /**
     * Returns data
     *
     * @return array
     */
    public function getData()
    {
        if (!isset($this->_loadedData)) {
            $this->_loadedData = [];
            foreach ($this->getCollection() as $scheduler) {
                $id = $scheduler->getId();
                $data = $scheduler->getData();
                $data['json_data'] = json_encode(json_decode($data['json_data'], true), JSON_PRETTY_PRINT);
                $this->_loadedData[$id] = $data;
            }
        }

        return $this->_loadedData;
    }
}