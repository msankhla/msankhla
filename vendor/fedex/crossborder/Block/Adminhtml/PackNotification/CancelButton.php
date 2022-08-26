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

class CancelButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getButtonData()
    {
        return ($this->getHelper()->getCurrentPackNotification()->canCancel() ? [
            'label'         => __('Cancel'),
            'on_click'      => sprintf("location.href = '%s';", $this->getUrl('*/*/cancel', ['pack_id' => $this->getPackNotificationId()])),
            'class'         => 'cancel',
            'id'            => 'cancel-button',
            'sort_order'    => 10
        ] : []);
    }
}
