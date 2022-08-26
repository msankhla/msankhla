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
namespace FedEx\CrossBorder\Plugin\Quote\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use FedEx\CrossBorder\Model\ProductValidator;

class AbstractItemPlugin
{
    const ERROR_QUOTE           = 'Some items in your cart are not available in your selected ship-to destination. Please remove them before checking out.';
    const ERROR_QUOTE_ITEM      = 'Please remove item from your cart to continue checkout.';
    /**
     * @var ProductValidator
     */
    protected $_productValidator;

    /**
     * ConfigurablePlugin constructor.
     *
     * @param ProductValidator $productValidator
     */
    public function __construct(
        ProductValidator $productValidator
    ) {
        $this->_productValidator = $productValidator;
    }

    /**
     * @param AbstractItem $subject
     * @param $proceed
     * @return AbstractItem
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundCheckData(
        AbstractItem $subject,
        $proceed
    ) {
        $result = $proceed();
        $isValid = true;

        if ($subject->getHasChildren()) {
            foreach ($subject->getChildren() as $child) {
                if (!$this->_productValidator->isProductAvailable($child->getProduct())) {
                    $isValid = false;
                    break;
                }
            }
        } elseif (!$this->_productValidator->isProductAvailable($subject->getProduct())) {
            $isValid = false;
        }

        if (!$isValid) {
            $subject->setHasError(
                true
            )->setMessage(
                __(static::ERROR_QUOTE_ITEM)
            )->getQuote()->setHasError(
                true
            )->addMessage(
                __(static::ERROR_QUOTE)
            );
        }

        return $result;
    }
}