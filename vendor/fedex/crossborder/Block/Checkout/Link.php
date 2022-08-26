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

class Link extends Template
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * Link constructor.
     *
     * @param Helper $helper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Helper $helper,
        Context $context,
        array $data = []
    ) {
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
        return $this->getHelper()->isInternational();
    }

    /**
     * Returns domestic checkout url
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('fdxcb/checkout/domestic');
    }

    /**
     * Returns helper
     *
     * @return Helper
     */
    public function getHelper()
    {
        return $this->_helper;
    }
}
