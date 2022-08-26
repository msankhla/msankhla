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
namespace FedEx\CrossBorder\Block\Order\View;

use FedEx\CrossBorder\Api\Data\OrderLinkInterface;
use FedEx\CrossBorder\Model\MerchantControl;
use FedEx\CrossBorder\Model\MonitorApp\Widget as MonitorApp;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class Info extends Template
{
    /**
     * @var MerchantControl
     */
    protected $_merchantControl;

    /**
     * @var MonitorApp
     */
    protected $_monitorApp;

    /**
     * @var string
     */
    protected $_monitorAppHtml;

    /**
     * @var OrderLinkInterface
     */
    protected $_orderLink;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var string
     */
    protected $_template = 'order/view/info.phtml';

    /**
     * Info constructor.
     *
     * @param MerchantControl $merchantControl
     * @param MonitorApp $monitorApp
     * @param Registry $registry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        MerchantControl $merchantControl,
        MonitorApp $monitorApp,
        Registry $registry,
        Context $context,
        array $data = []
    ) {
        $this->_merchantControl = $merchantControl;
        $this->_monitorApp = $monitorApp;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Checks if information can be shown
     *
     * @return bool
     */
    public function canShow()
    {
        return (bool) $this->getOrderLink();
    }

    /**
     * Checks if Monito App can be shown
     *
     * @return bool
     */
    public function canShowMonitorApp()
    {
        return $this->_monitorApp->canShow();
    }

    /**
     * Checks if tracking link can be shown
     *
     * @return bool
     */
    public function canShowTrackingLink()
    {
        return !$this->_merchantControl->isEnabled() || $this->_merchantControl->canShowTrackingLink();
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_registry->registry('current_order');
    }

    /**
     * Returns order link
     *
     * @return OrderLinkInterface
     */
    public function getOrderLink()
    {
        if (!isset($this->_orderLink)) {
            $extensionAttributes = $this->getOrder()->getExtensionAttributes();
            if ($extensionAttributes) {
                $this->_orderLink = $extensionAttributes->getFdxcbData();
            }
        }

        return $this->_orderLink;
    }

    /**
     * Returns monitor app html
     *
     * @return string
     */
    public function getMonitorAppHtml()
    {
        if (!isset($this->_monitorAppHtml)) {
            $this->_monitorAppHtml = $this->_monitorApp->toHtml();
        }

        return $this->_monitorAppHtml;
    }
}