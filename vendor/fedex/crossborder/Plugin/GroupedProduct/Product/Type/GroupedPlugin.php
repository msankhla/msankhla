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
namespace FedEx\CrossBorder\Plugin\GroupedProduct\Product\Type;

use FedEx\CrossBorder\Model\ProductValidator;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;

class GroupedPlugin
{
    /**
     * @param Grouped $subject
     * @param Collection $result
     * @return Collection
     */
    public function afterGetAssociatedProductCollection(
        Grouped $subject,
        Collection $result
    ) {
        $result->addAttributeToSelect(
            [
                ProductValidator::COO_ATTRIBUTE_CODE,
                ProductValidator::IMPORT_FLAG_ATTRIBUTE_CODE,
                ProductValidator::HAZ_FLAG_ATTRIBUTE_CODE,
            ]
        );

        return $result;
    }
}