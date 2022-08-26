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
namespace FedEx\CrossBorder\Plugin\Wishlist\CustomerData;

use FedEx\CrossBorder\Model\ProductValidator;
use Magento\Wishlist\CustomerData\Wishlist;
use Magento\Catalog\Api\ProductRepositoryInterface;

class WishlistPlugin
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;
    /**
     * @var ProductValidator
     */
    protected $_productValidator;

    /**
     * WishlistPlugin constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ProductValidator $productValidator
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductValidator $productValidator
    ) {
        $this->_productRepository = $productRepository;
        $this->_productValidator = $productValidator;
    }

    /**
     * Plugin for getSectionData
     *
     * @param Wishlist $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(
        Wishlist $subject,
        array $result
    ) {
        foreach ($result['items'] as &$item) {
            $product = $this->_productRepository->getById($item['product_id']);
            if (!$this->_productValidator->isProductAvailable($product)) {
                $item['product_is_saleable_and_visible'] = false;
            }
        }

        return $result;
    }
}