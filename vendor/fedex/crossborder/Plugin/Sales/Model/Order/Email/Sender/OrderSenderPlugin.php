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
namespace FedEx\CrossBorder\Plugin\Sales\Model\Order\Email\Sender;

use FedEx\CrossBorder\Api\OrderLinkManagementInterface;
use FedEx\CrossBorder\Helper\Data as Helper;
use Magento\Framework\App\State;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class OrderSenderPlugin
{
    /**
     * @var OrderLinkManagementInterface
     */
    protected $_orderLinkManagement;

    /**
     * @var State
     */
    protected $_state;

    /**
     * OrderSenderPlugin constructor.
     *
     * @param OrderLinkManagementInterface $orderLinkManagement
     * @param State $state
     */
    public function __construct(
        OrderLinkManagementInterface $orderLinkManagement,
        State $state
    ) {
        $this->_orderLinkManagement = $orderLinkManagement;
        $this->_state = $state;
    }

    /**
     * Around send plugin
     *
     * @param OrderSender $subject
     * @param callable $proceed
     * @param Order $order
     * @param bool $forceSyncMode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundSend(
        OrderSender $subject,
        $proceed,
        Order $order,
        $forceSyncMode = false
    ) {
        if (in_array($this->_state->getAreaCode(), ['crontab', 'webapi_rest']) && !$forceSyncMode) {
            $this->_orderLinkManagement->setFdxcbData($order);
            if ($order->getExtensionAttributes() &&
                $order->getExtensionAttributes()->getFdxcbData() &&
                !$this->getHelper()->isOrderConfirmationEmail()
            ) {
                return false;
            }
        }

        return $proceed($order, $forceSyncMode);
    }

    /**
     * @return Helper
     */
    public function getHelper()
    {
        return $this->_orderLinkManagement->getMerchantControl()->getHelper();
    }
}
