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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use FedEx\CrossBorder\Model\ProductValidator;

class Cart implements ObserverInterface
{
    /**
     * @var ProductValidator
     */
    protected $_productValidator;

    /**
     * Cart constructor.
     *
     * @param ProductValidator $productValidator
     */
    public function __construct(
        ProductValidator $productValidator
    ) {
        $this->_productValidator = $productValidator;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getProduct();
        if (!$this->_productValidator->isProductAvailable($product)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(ProductValidator::ERROR_PRODUCT_NOT_AVAILABLE)
            );
        }
    }
}