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
namespace FedEx\CrossBorder\Model;

use Magento\Sales\Api\Data\OrderInterface;
use FedEx\CrossBorder\Api\Data\SchedulerInterface;
use FedEx\CrossBorder\Api\Data\SchedulerInterfaceFactory;
use FedEx\CrossBorder\Api\SchedulerManagementInterface;
use FedEx\CrossBorder\Model\ResourceModel\Scheduler as ResourceModel;
use FedEx\CrossBorder\Model\ResourceModel\Scheduler\Collection;

class SchedulerManagement implements SchedulerManagementInterface
{
    /**
     * @var SchedulerInterfaceFactory
     */
    protected $_schedulerFactory;

    /**
     * SchedulerManager constructor.
     *
     * @param SchedulerInterfaceFactory $schedulerFactory
     */
    public function __construct(
        SchedulerInterfaceFactory $schedulerFactory
    ) {
        $this->_schedulerFactory = $schedulerFactory;
    }

    /**
     * Merges arrays width ids
     *
     * @param array $arrays
     * @return array
     */
    protected function _mergeIds($arrays)
    {
        $result = [];
        if (is_array($arrays)) {
            foreach ($arrays as $array) {
                foreach ($array as $id) {
                    $result[$id] = $id;
                }
            }
        } else {
            $result = [];
        }

        return array_values($result);
    }

    /**
     * Merges data
     *
     * @param array $data
     * @param mixed $item
     * @return array
     */
    protected function _mergeData($data, $item)
    {
        $isExist = false;
        foreach ($data as $_item) {
            if ($item == $_item) {
                $isExist = true;
                break;
            }
        }

        if (!$isExist) {
            $data[] = $item;
        }

        return $data;
    }

    /**
     * Creates or updates scheduler array data
     *
     * @param string $type
     * @param array $data
     * @return $this
     */
    protected function _addArrayData($type, $data)
    {
        if (is_array($data)) {
            $scheduler = $this->getSchedulerByType($type);
            if ($scheduler->getId()) {
                $jsonData = json_decode($scheduler->getJsonData(), true);
                $jsonData['orders'] = $this->_mergeData(
                    (isset($jsonData['orders']) ? $jsonData['orders'] : []),
                    $data
                );
                $scheduler->setJsonData(
                    json_encode($jsonData)
                )->save();
            } else {
                $this->addScheduler(
                    $type,
                    ['orders' => [$data]]
                );
            }
        }

        return $this;
    }

    /**
     * Creates or updates scheduler ids data
     *
     * @param string $type
     * @param int|array $ids
     * @return $this
     */
    protected function _addIdsData($type, $ids)
    {
        $ids = (array) $ids;
        $scheduler = $this->getSchedulerByType($type);
        if ($scheduler->getId()) {
            $jsonData = json_decode($scheduler->getJsonData(), true);
            $jsonData['ids'] = $this->_mergeIds([
                $jsonData['ids'],
                $ids
            ]);
            $scheduler->setJsonData(
                json_encode($jsonData)
            )->save();
        } else {
            $this->addScheduler(
                $type,
                ['ids' => $ids]
            );
        }

        return $this;
    }

    /**
     * Creates or updates product scheduler
     *
     * @param int|array $id
     * @return $this
     */
    public function addProductScheduler($id)
    {
        return $this->_addIdsData(SchedulerInterface::TYPE_PRODUCT, $id);
    }

    /**
     * Creates or updates order scheduler
     *
     * @param OrderInterface $order
     * @return $this
     */
    public function addOrderScheduler($order)
    {
        if ($order && $order->getId() &&
            !$order->isCanceled() &&
            $order->getStatus() !== OrderStatusManagement::STATUS_CANCELLATION_REQUEST
        ) {
            $extensionAttributes = $order->getExtensionAttributes();
            if ($extensionAttributes) {
                $orderLink = $extensionAttributes->getFdxcbData();
                if ($orderLink && $orderLink->getId()) {
                    $this->_addArrayData(
                        SchedulerInterface::TYPE_ORDER,
                        [
                            'id'                    => $order->getId(),
                            'status'                => $order->getState(),
                            'fxcb_order_number'     => $orderLink->getFxcbOrderNumber(),
                        ]
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Creates new scheduler
     *
     * @param string $type
     * @param mixed $data
     * @return $this
     */
    public function addScheduler($type, $data)
    {
        $scheduler = $this->getScheduler();
        $scheduler->setType(
            $type
        )->setStatus(
            SchedulerInterface::STATUS_NEW
        )->setJsonData(
            json_encode($data)
        )->save();

        return $this;
    }

    /**
     * Returns all available schedulers
     *
     * @return Collection
     */
    public function getAvailableSchedulers()
    {
        /** @var Collection $collection */
        $collection = $this->_schedulerFactory->create()->getResourceCollection();
        $collection->addFieldToFilter(
            SchedulerInterface::STATUS,
            [SchedulerInterface::STATUS_NEW, SchedulerInterface::STATUS_ERROR]
        )->setOrder(SchedulerInterface::UPDATED_AT, 'ASC');

        return $collection;
    }

    /**
     * Returns scheduler
     *
     * @param int|null $id
     * @return SchedulerInterface
     */
    public function getScheduler($id = null)
    {
        /** @var SchedulerInterface $scheduler */
        $scheduler = $this->_schedulerFactory->create();
        return $scheduler->load($id);
    }

    /**
     * Returns scheduler by type with status "new"
     *
     * @param string $type
     * @return SchedulerInterface
     */
    public function getSchedulerByType($type)
    {
        /** @var Collection $collection */
        $collection = $this->_schedulerFactory->create()->getResourceCollection();
        $collection->addFieldToFilter(
            SchedulerInterface::TYPE,
            $type
        )->addFieldToFilter(
            SchedulerInterface::STATUS,
            [SchedulerInterface::STATUS_NEW, SchedulerInterface::STATUS_ERROR]
        )->setOrder(SchedulerInterface::UPDATED_AT, 'ASC');

        return $collection->getFirstItem();
    }

    /**
     * Start process for all available schedulers
     *
     * @return $this
     */
    public function start()
    {
        foreach ($this->getAvailableSchedulers() as $scheduler) {
            $scheduler->run(true);
        }

        return $this;
    }
}