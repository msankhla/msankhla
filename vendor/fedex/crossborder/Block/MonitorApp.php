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
namespace FedEx\CrossBorder\Block;

use FedEx\CrossBorder\Model\MonitorApp\Widget as MonitorAppWidget;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class MonitorApp extends Template
{
    /**
     * @var string
     */
    protected $_template    = 'monitor_app.phtml';

    /**
     * @var MonitorAppWidget
     */
    protected $_monitorApp;

    /**
     * @var string
     */
    protected $_monitorAppHtml;

    /**
     * MonitorApp constructor.
     *
     * @param MonitorAppWidget $monitorApp
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        MonitorAppWidget $monitorApp,
        Context $context,
        array $data = []
    ) {
        $this->_monitorApp = $monitorApp;
        parent::__construct($context, $data);
    }

    /**
     * Returns Monitor App widget html code
     *
     * @return string
     */
    public function getWidgetHtml()
    {
        if (!isset($this->_monitorAppHtml)) {
            $this->_monitorAppHtml = $this->_monitorApp->toHtml(true);
        }

        return $this->_monitorAppHtml;
    }
}