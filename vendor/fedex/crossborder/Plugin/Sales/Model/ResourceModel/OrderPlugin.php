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
namespace FedEx\CrossBorder\Plugin\Sales\Model\ResourceModel;

use FedEx\CrossBorder\Api\SchedulerManagementInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order;

class OrderPlugin
{
    /**
     * @var SchedulerManagementInterface
     */
    protected $_schedulerManagement;

    /**
     * OrderPlugin constructor.
     *
     * @param SchedulerManagementInterface $schedulerManagement
     */
    public function __construct(SchedulerManagementInterface $schedulerManagement)
    {
        $this->_schedulerManagement = $schedulerManagement;
    }

    public function afterSave(
        Order $subject,
        $result,
        AbstractModel $object
    ) {
        if (($object->dataHasChangedFor(OrderInterface::STATUS) || $object->dataHasChangedFor(OrderInterface::STATE))) {
            $this->_schedulerManagement->addOrderScheduler($object);
        }

        return $result;
    }
}