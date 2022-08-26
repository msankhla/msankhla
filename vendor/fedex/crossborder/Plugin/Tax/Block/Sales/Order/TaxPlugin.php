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
namespace FedEx\CrossBorder\Plugin\Tax\Block\Sales\Order;

use FedEx\CrossBorder\Model\OrderLinkManagement;
use Magento\Tax\Block\Sales\Order\Tax;
use Magento\Sales\Model\Order;

class TaxPlugin
{
    /**
     * @var OrderLinkManagement
     */
    protected $_orderLinkManagement;

    /**
     * TaxPlugin constructor.
     *
     * @param OrderLinkManagement $orderLinkManagement
     */
    public function __construct(
        OrderLinkManagement $orderLinkManagement
    ) {
        $this->_orderLinkManagement = $orderLinkManagement;
    }

    /**
     * After getOrder() plugin
     *
     * @param Tax $subject
     * @param Order $result
     * @return Order
     */
    public function afterGetOrder(
        Tax $subject,
        Order $result
    ) {
        if ($result && $result->getId()) {
            $this->_orderLinkManagement->setFdxcbData($result);
        }

        return $result;
    }

}