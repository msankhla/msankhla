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
namespace FedEx\CrossBorder\Controller\Adminhtml\PackNotification;

use FedEx\CrossBorder\Controller\Adminhtml\PackNotification as PackNotificationAction;
use Magento\Framework\Exception\NoSuchEntityException;

class View extends PackNotificationAction
{
    /**
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        if ($this->isValid()) {
            $this->_registry->register(
                'current_pack_notification',
                $this->_helper->getCurrentPackNotification()
            );

            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Sales::sales_order');
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__(
                '%1 for Order #%2',
                (
                    $this->_helper->getCurrentPackNotificationId() ?
                    'Pack Notification #' . $this->_helper->getCurrentPackNotificationId() :
                    'New Pack Notification'
                ),
                $this->_helper->getCurrentOrder()->getIncrementId()
            ));
            $this->_view->renderLayout();
        } else {
            $this->_redirect('sales/order/view', ['order_id' => $this->_helper->getOrderId()]);
        }
    }
}
