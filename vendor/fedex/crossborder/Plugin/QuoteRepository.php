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
use FedEx\CrossBorder\Api\QuoteLinkManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

class QuoteRepository
{
    /**
     * @var QuoteLinkManagementInterface
     */
    protected $_quoteLinkManagement;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * QuoteRepository constructor.
     *
     * @param Helper $helper
     * @param QuoteLinkManagementInterface $quoteLinkManagement
     */
    public function __construct(
        Helper $helper,
        QuoteLinkManagementInterface $quoteLinkManagement
    ) {
        $this->_helper = $helper;
        $this->_quoteLinkManagement  = $quoteLinkManagement;
    }

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param CartInterface $cart
     * @return CartInterface
     */
    public function afterGet(
        CartRepositoryInterface $cartRepository,
        CartInterface $cart
    ) {
        if ($this->_helper->isEnabled()) {
            $this->_quoteLinkManagement->setFdxcbData($cart);
        }

        return $cart;
    }

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param CartInterface $cart
     * @return CartInterface
     */
    public function afterGetActive(
        CartRepositoryInterface $cartRepository,
        CartInterface $cart
    ) {
        if ($this->_helper->isEnabled()) {
            $this->_quoteLinkManagement->setFdxcbData($cart);
        }

        return $cart;
    }

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param CartInterface $cart
     * @return CartInterface
     */
    public function afterGetActiveForCustomer(
        CartRepositoryInterface $cartRepository,
        CartInterface $cart
    ) {
        if ($this->_helper->isEnabled()) {
            $this->_quoteLinkManagement->setFdxcbData($cart);
        }

        return $cart;
    }

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param CartInterface $cart
     * @return CartInterface
     */
    public function afterGetForCustomer(
        CartRepositoryInterface $cartRepository,
        CartInterface $cart
    ) {
        if ($this->_helper->isEnabled()) {
            $this->_quoteLinkManagement->setFdxcbData($cart);
        }

        return $cart;
    }
}
