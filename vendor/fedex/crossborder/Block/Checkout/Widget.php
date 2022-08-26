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
namespace FedEx\CrossBorder\Block\Checkout;

use \Magento\Framework\View\Element\Template;
use \Magento\Framework\View\Element\Template\Context;
use \FedEx\CrossBorder\Helper\Data as Helper;
use \FedEx\CrossBorder\Model\Checkout\Widget as CheckoutWidget;

class Widget extends Template
{
    /**
     * @var CheckoutWidget
     */
    protected $_checkoutWidget;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var string
     */
    protected $_template = 'checkout/widget.phtml';

    /**
     * Widget constructor.
     *
     * @param CheckoutWidget $checkoutWidget
     * @param Helper $helper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        CheckoutWidget $checkoutWidget,
        Helper $helper,
        Context $context,
        array $data = []
    ) {
        $this->_checkoutWidget = $checkoutWidget;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Checks if the link can be displayed
     *
     * @return bool
     */
    public function canShow()
    {
        return $this->_helper->isInternational() && $this->getCheckoutWidget();
    }

    /**
     * Returns checkout widget code
     *
     * @return string
     */
    public function getCheckoutWidget()
    {
        return $this->_checkoutWidget->toHtml();
    }
}