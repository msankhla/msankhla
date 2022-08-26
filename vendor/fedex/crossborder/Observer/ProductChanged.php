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
namespace FedEx\CrossBorder\Observer;

use FedEx\CrossBorder\Helper\Data as Helper;
use FedEx\CrossBorder\Api\SchedulerManagementInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductChanged implements ObserverInterface
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var SchedulerManagementInterface
     */
    protected $_schedulerManagement;

    /**
     * ProductChanged constructor.
     *
     * @param Helper $helper
     * @param SchedulerManagementInterface $schedulerManagement
     */
    public function __construct(
        Helper $helper,
        SchedulerManagementInterface $schedulerManagement
    ) {
        $this->_helper = $helper;
        $this->_schedulerManagement = $schedulerManagement;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->_helper->isEnabled()) {
            $this->_schedulerManagement->addProductScheduler(
                $observer->getProduct()->getId()
            );
        }
    }
}
