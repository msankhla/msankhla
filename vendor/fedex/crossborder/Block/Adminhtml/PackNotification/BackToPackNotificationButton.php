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

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BackToPackNotificationButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label'         => __('Back'),
            'on_click'      => sprintf("location.href = '%s';", $this->getUrl('*/*/view', $this->getParams())),
            'class'         => 'back',
            'sort_order'    => 10
        ];
    }
}
