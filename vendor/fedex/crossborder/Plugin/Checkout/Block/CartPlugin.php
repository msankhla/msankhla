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
namespace FedEx\CrossBorder\Plugin\Checkout\Block;

use Magento\Checkout\Block\Cart;
use FedEx\CrossBorder\Helper\Data as Helper;

class CartPlugin
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * CartPlugin constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * @param Cart $subject
     * @param callable $proceed
     * @param string $alias
     * @return array
     */
    public function aroundGetMethods(
        Cart $subject,
        $proceed,
        $alias
    ) {
        if ($this->_helper->isInternational()) {
            return ['checkout.cart.methods.fedex.crossborder.link'];
        }

        return $proceed($alias);
    }
}