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
namespace FedEx\CrossBorder\Plugin\Sales\Block\Order;

use FedEx\CrossBorder\Block\Adminhtml\Order\View\PackNotificationForm;
use FedEx\CrossBorder\Helper\PackNotification as Helper;
use FedEx\CrossBorder\Model\OrderStatusManagement;
use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Sales\Model\Order;
use Magento\Framework\View\LayoutInterface;

class ViewPlugin
{
    const MSG_ORDER_CANCEL_REQUEST  = 'Are you sure you want to send an order cancellation request?';

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * ViewPlugin constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Checks if cancel functionality is available
     *
     * @param Order $order
     * @return bool
     */
    protected function _canCancel(Order $order)
    {
        return $this->_helper->getMerchantControl()->isEnabled() &&
            $order->getState() == Order::STATE_PROCESSING &&
            $order->getStatus() !== OrderStatusManagement::STATUS_CANCELLATION_REQUEST;
    }

    /**
     * Checks if pack notification functionality can be shown
     *
     * @param Order $order
     * @return bool
     */
    protected function _canShow(Order $order)
    {
        return $this->_helper->isAvailable($order);
    }

    /**
     * Checks if order placed via FedEx
     *
     * @param Order $order
     * @return bool
     */
    protected function _isFdxOrder(Order $order)
    {
        $orderLink = $this->_helper->getOrderLink($order);
        return $orderLink && $orderLink->getFxcbOrderNumber();
    }

    /**
     * Adds pack notification button
     *
     * @param View $subject
     * @param LayoutInterface $layout
     * @return array
     */
    public function beforeSetLayout(
        View $subject,
        LayoutInterface $layout
    ) {
        $order = $subject->getOrder();
        if ($this->_canShow($order)) {
            $subject->addButton(
                'pack_notification_button',
                [
                    'label'     => __('Pack Notification'),
                    'class'     => __('pack-notification-button'),
                    'id'        => 'pack-notification-button',
                    'onclick'   => 'setLocation(\'' . $subject->getUrl('fdxcb/packNotification/start') . '\')',
                ],
                0,
                -1
            );
        }

        if ($this->_isFdxOrder($order)) {
            $subject->removeButton('order_cancel');
            if ($this->_canCancel($order)) {
                $subject->addButton(
                    'order_cancel_request',
                    [
                        'label'     => __('Cancellation Request'),
                        'class'     => __('order-cancellation-request-button'),
                        'id'        => 'order-cancellation-request-button',
                        'onclick'   => sprintf(
                            "confirmSetLocation('%s', '%s')",
                            __(static::MSG_ORDER_CANCEL_REQUEST),
                            $subject->getUrl('fdxcb/order/cancel')
                        ),
                    ],
                    0,
                    -2
                );
            }
        }

        return [$layout];
    }
}