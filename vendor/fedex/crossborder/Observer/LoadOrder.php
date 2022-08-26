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
namespace FedEx\CrossBorder\Observer;

use FedEx\CrossBorder\Helper\Data as Helper;
use FedEx\CrossBorder\Api\OrderLinkManagementInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class LoadOrder implements ObserverInterface
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var OrderLinkManagementInterface
     */
    protected $_orderLinkManagement;

    /**
     * LoadOrder constructor.
     *
     * @param Helper $helper
     * @param OrderLinkManagementInterface $orderLinkManagement
     */
    public function __construct(
        Helper $helper,
        OrderLinkManagementInterface $orderLinkManagement
    ) {
        $this->_helper = $helper;
        $this->_orderLinkManagement = $orderLinkManagement;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->_helper->isEnabled()) {
            $this->_orderLinkManagement->setFdxcbData(
                $observer->getOrder()
            );
        }
    }
}
