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

use Magento\Backend\App\Action;

class Index extends Action
{
    const ADMIN_RESOURCE = 'FedEx_CrossBorder::scheduler';

    /**
     * Start create pack notification action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('FedEx_CrossBorder::menu');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('API Scheduler'));
        $this->_view->renderLayout();
    }
}