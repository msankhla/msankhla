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
namespace FedEx\CrossBorder\Plugin;

use FedEx\CrossBorder\Helper\Data as Helper;
use FedEx\CrossBorder\Api\OrderLinkManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;

class OrderRepository
{
    /**
     * @var OrderLinkManagementInterface
     */
    protected $_orderLinkManagement;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * QuoteRepository constructor.
     *
     * @param Helper $helper
     * @param OrderLinkManagementInterface $orderLinkManagement
     */
    public function __construct(
        Helper $helper,
        OrderLinkManagementInterface $orderLinkManagement
    ) {
        $this->_helper = $helper;
        $this->_orderLinkManagement  = $orderLinkManagement;
    }

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderInterface $order
     * @return mixed
     */
    public function afterGet(
        OrderRepositoryInterface $orderRepository,
        OrderInterface $order
    ) {
        if ($this->_helper->isEnabled()) {
            $this->_orderLinkManagement->setFdxcbData($order);
        }

        return $order;
    }

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderSearchResultInterface $searchResult
     * @return OrderSearchResultInterface
     */
    public function afterGetList(
        OrderRepositoryInterface $orderRepository,
        OrderSearchResultInterface $searchResult
    ) {
        if ($this->_helper->isEnabled()) {
            $orders = $searchResult->getItems();
            foreach ($orders as &$order) {
                $this->_orderLinkManagement->setFdxcbData($order);
            }
        }

        return $searchResult;
    }
}
