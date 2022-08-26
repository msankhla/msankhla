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
namespace FedEx\CrossBorder\Block\Adminhtml\PackNotification;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Returns button data
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getButtonData()
    {
        $label = __($this->getPackNotificationId() ? 'Back to Pack Notifications' : 'Back to Order');
        $params = ['order_id' => $this->getOrderId()];
        if ($this->getPackNotificationId()) {
            $params['active_tab'] = 'order_pack_notification';
        }
        return [
            'label'         => $label,
            'on_click'      => sprintf("location.href = '%s';", $this->getUrl('sales/order/view', $params)),
            'class'         => 'back',
            'sort_order'    => 10
        ];
    }
}
