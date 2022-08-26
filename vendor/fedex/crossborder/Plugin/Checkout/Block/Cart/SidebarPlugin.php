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
namespace FedEx\CrossBorder\Plugin\Checkout\Block\Cart;

use Magento\Checkout\Block\Cart\Sidebar;
use Magento\Framework\UrlInterface;
use FedEx\CrossBorder\Helper\Data as Helper;

class SidebarPlugin
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * SidebarPlugin constructor.
     *
     * @param Helper $helper
     * @param UrlInterface $url
     */
    public function __construct(
        Helper $helper,
        UrlInterface $url
    ) {
        $this->_helper = $helper;
        $this->_url = $url;
    }

    /**
     * @param Sidebar $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(
        Sidebar $subject,
        array $result
    ) {
        $result['isDomestic'] = !$this->_helper->isInternational();
        if (!$result['isDomestic']) {
            $result['defaultCountry'] = $this->_helper->getDefaultCountry();
            $result['checkoutDomesticUrl'] = $this->_url->getUrl('fdxcb/checkout/domestic');
        }

        return $result;
    }
}