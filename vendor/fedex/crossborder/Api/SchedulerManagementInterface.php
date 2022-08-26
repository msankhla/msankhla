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
namespace FedEx\CrossBorder\Api;

interface SchedulerManagementInterface
{
    /**
     * Creates or updates product scheduler
     *
     * @param int|array $id
     * @return SchedulerManagementInterface
     */
    public function addProductScheduler($id);

    /**
     * Creates or updates order scheduler
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return SchedulerManagementInterface
     */
    public function addOrderScheduler($order);

    /**
     * Creates new scheduler
     *
     * @param string $type
     * @param mixed $data
     * @return SchedulerManagementInterface
     */
    public function addScheduler($type, $data);

    /**
     * Returns scheduler
     *
     * @param int|null $id
     * @return \FedEx\CrossBorder\Api\Data\SchedulerInterface
     */
    public function getScheduler($id = null);

    /**
     * Returns scheduler by type with status "new"
     *
     * @param string $type
     * @return \FedEx\CrossBorder\Api\Data\SchedulerInterface
     */
    public function getSchedulerByType($type);
}