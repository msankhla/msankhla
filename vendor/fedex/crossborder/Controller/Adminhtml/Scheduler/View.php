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
namespace FedEx\CrossBorder\Controller\Adminhtml\Scheduler;

use FedEx\CrossBorder\Api\Data\SchedulerInterface;
use FedEx\CrossBorder\Api\Data\SchedulerInterfaceFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

class View extends Action
{
    const ADMIN_RESOURCE    = 'FedEx_CrossBorder::scheduler';
    const ERROR_NOT_FOUND   = 'This scheduler doesn\'t exist.';

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var SchedulerInterfaceFactory
     */
    protected $_schedulerFactory;

    /**
     * View constructor.
     *
     * @param Registry $registry
     * @param SchedulerInterfaceFactory $schedulerFactory
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        SchedulerInterfaceFactory $schedulerFactory,
        Context $context
    ) {
        $this->_registry = $registry;
        $this->_schedulerFactory = $schedulerFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $scheduler = $this->_schedulerFactory->create()->load($id);
        if ($scheduler->getId()) {
            $this->_registry->register('current_scheduler', $scheduler);
            $this->_view->loadLayout();
            $this->_setActiveMenu('FedEx_CrossBorder::menu');
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Scheduler #%1', $id));
            $this->_view->renderLayout();
        } else {
            $this->messageManager->addErrorMessage(static::ERROR_NOT_FOUND);
            $this->_redirect('*/*/index');
        }
    }
}