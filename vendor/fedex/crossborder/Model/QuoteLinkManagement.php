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
namespace FedEx\CrossBorder\Model;

use FedEx\CrossBorder\Api\QuoteLinkManagementInterface;
use FedEx\CrossBorder\Api\Data\QuoteLinkInterfaceFactory;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;

class QuoteLinkManagement implements QuoteLinkManagementInterface
{
    /**
     * @var CartExtensionFactory
     */
    protected $_cartExtensionFactory;

    /**
     * @var QuoteLinkInterfaceFactory
     */
    protected $_quoteLinkFactory;

    /**
     * QuoteLinkManagement constructor.
     *
     * @param CartExtensionFactory $cartExtensionFactory
     * @param QuoteLinkInterfaceFactory $quoteLinkFactory
     */
    public function __construct(
        CartExtensionFactory $cartExtensionFactory,
        QuoteLinkInterfaceFactory $quoteLinkFactory
    ) {
        $this->_cartExtensionFactory = $cartExtensionFactory;
        $this->_quoteLinkFactory     = $quoteLinkFactory;
    }

    /**
     * Link FedEx data to cart quote
     *
     * @param CartInterface $cart
     * @return void
     */
    public function setFdxcbData(CartInterface $cart)
    {
        $cartExtension = ($cart->getExtensionAttributes()) ?: $this->_cartExtensionFactory->create();

        $quoteLink = $this->_quoteLinkFactory->create();
        $quoteLink->load($cart->getId(), 'quote_id');

        if ($quoteLink->getId()) {
            $cartExtension->setFdxcbData($quoteLink);
        }

        $cart->setExtensionAttributes($cartExtension);
    }
}
