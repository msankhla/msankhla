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

use FedEx\CrossBorder\Helper\Data as Helper;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;

class ProductValidator
{
    const COO_ATTRIBUTE_CODE            = 'fdx_country_of_origin';
    const IMPORT_FLAG_ATTRIBUTE_CODE    = 'fdx_import_flag';
    const HAZ_FLAG_ATTRIBUTE_CODE       = 'fdx_haz_flag';
    const CONFIG_PATH                   = 'fedex_crossborder/product_validation/';

    const ERROR_PRODUCT_NOT_AVAILABLE   = 'The product isn\'t available for international shipping';
    const ERROR_PRODUCT_NOT_FOUND       = 'The product does not exist';
    const ERROR_REMOVE_ITEM             = 'Please remove item from your cart to continue checkout';

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * ProductValidator constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_helper = $helper;
        $this->_productRepository = $productRepository;
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

    /**
     * Returns product options
     *
     * @param Product $product
     * @return SelectionCollection|array
     */
    public function getProductOptions(Product $product)
    {
        $result = [];
        if ($product && $product->getId()) {
            if ($product->getTypeId() == 'bundle') {
                $typeInstance = $product->getTypeInstance();
                $typeInstance->setStoreFilter($product->getStoreId(), $product);

                /** @var SelectionCollection $selectionCollection */
                $result = $typeInstance->getSelectionsCollection(
                    $typeInstance->getOptionsIds($product),
                    $product
                );

            } elseif ($product->getTypeId() == 'grouped') {
                $result = $product->getTypeInstance()->getAssociatedProducts($product);
            }
        }

        return $result;
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getProductOptionsAvailability(Product $product) {
        $result = [];
        foreach ($this->getProductOptions($product) as $item) {
            $result[$item->getId()] = $this->isProductAvailable($item);
        }

        return $result;
    }

    /**
     * Checks if enabled
     *
     * @return bool
     */
    public function isCooEnabled()
    {
        return (bool) $this->getHelper()->isEnabled() && $this->getHelper()->getConfig(static::CONFIG_PATH . 'coo', 1);
    }

    /**
     * Checks if enabled
     *
     * @return bool
     */
    public function isImportFlagEnabled()
    {
        return (bool) $this->getHelper()->isEnabled() && $this->getHelper()->getConfig(static::CONFIG_PATH . 'import_flag', 1);
    }

    /**
     * Checks if enabled
     *
     * @return bool
     */
    public function isHazFlagEnabled()
    {
        return (bool) $this->getHelper()->isEnabled();
    }

    /**
     * Checks id product available for international shipping
     *
     * @param Product $product
     * @return bool
     */
    public function isProductAvailable($product)
    {
        $result = true;
        if ($product && $product->getId() && $this->getHelper()->isInternational()) {
            if (!$this->validateIdentifier($product)) {
                return false;
            }

            if ($this->isCooEnabled()) {
                $coo = $product->getFdxCountryOfOrigin();
                if (empty($coo)) {
                    $result = false;
                }
            }

            if ($this->isImportFlagEnabled()){
                $countries = explode(',', $product->getFdxImportFlag());
                if (in_array($this->getHelper()->getSelectedCountry(), $countries)) {
                    $result = false;
                }
            }

            if ($this->isHazFlagEnabled() && (bool) $product->getFdxHazFlag()){
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Validate if product identifier is defined and valid
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function validateIdentifier($product)
    {
        $code = $this->getHelper()->getProductIdentifier();
        if (!$product->hasData($code)) {
            $_product = clone $this->_productRepository->getById(
                $product->getId(),
                false,
                $product->getStoreId()
            );

            $identifier = $_product->getData($code);

            if (!empty($identifier)) {
                $product->setData($code, $identifier);
            }
        } else {
            $identifier = $product->getData($code);
        }

        return !empty($identifier);
    }
}