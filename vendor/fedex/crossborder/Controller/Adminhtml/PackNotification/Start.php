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

class Start extends PackNotificationAction
{
    /**
     * Start create pack notification action
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $this->_redirect('*/*/view', ['order_id' => $this->_helper->getOrderId()]);
    }
}
