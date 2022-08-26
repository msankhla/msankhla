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
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Cancel extends PackNotificationAction
{
    /**
     * Send Action
     *
     * @return Redirect
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $params = ['order_id' => $this->_helper->getOrderId()];
        if ($this->isValid()) {
            try {
                $this->_helper->getCurrentPackNotification()->cancel();

                $this->messageManager->addSuccessMessage(
                    __('The pack notification was canceled.')
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(
                    __($e->getMessage())
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __($e->getMessage())
                );
            }

            $params['active_tab'] = 'order_pack_notification';
        }

        $this->_redirect('sales/order/view', $params);
    }
}
