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
namespace FedEx\CrossBorder\Api;

use Magento\Quote\Api\Data\CartInterface;

/**
 * @api
 */
interface QuoteLinkManagementInterface
{
    /**
     * Link FedEx data to cart quote
     *
     * @param CartInterface $cart
     * @return void
     */
    public function setFdxcbData(CartInterface $cart);
}