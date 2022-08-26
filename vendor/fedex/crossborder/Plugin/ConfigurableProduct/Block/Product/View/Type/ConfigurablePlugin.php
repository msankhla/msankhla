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
namespace FedEx\CrossBorder\Plugin\ConfigurableProduct\Block\Product\View\Type;

use FedEx\CrossBorder\Model\ProductValidator;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;

class ConfigurablePlugin
{
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
     * @param Configurable $subject
     * @param callable $proceed
     * @return \Magento\Catalog\Model\Product[]
     */
    public function _aroundGetAllowProducts(
        Configurable $subject,
        callable $proceed
    ) {

        if (!$subject->hasAllowProducts()) {
            $products = [];
            $allProducts = $subject->getProduct()->getTypeInstance()->getUsedProducts($subject->getProduct(), null);
            /** @var $product \Magento\Catalog\Model\Product */
            foreach ($allProducts as $product) {
                if ((int) $product->getStatus() === Status::STATUS_ENABLED && $this->_productValidator->isProductAvailable($product)) {
                    $products[] = $product;
                }
            }

            $subject->setAllowProducts($products);
        }

        return $subject->getData('allow_products');
    }

    public function afterGetJsonConfig(
        Configurable $subject,
        $result
    ) {
        if ($this->_productValidator->getHelper()->isInternational()) {
            $data = json_decode($result, true);

            if (is_array($data)) {
                $data['isInternational'] = true;
                $data['productsAvailability'] = [];
                foreach ($subject->getAllowProducts() as $product) {
                    $data['productsAvailability'][$product->getId()] = $this->_productValidator->isProductAvailable($product);
                }

                $result = json_encode($data);
            }
        }

        return $result;
    }
}