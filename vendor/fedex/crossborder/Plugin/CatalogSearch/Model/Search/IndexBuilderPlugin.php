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
namespace FedEx\CrossBorder\Plugin\CatalogSearch\Model\Search;

use Magento\Catalog\Model\Product;
use Magento\CatalogSearch\Model\Search\IndexBuilder;
use Magento\Eav\Model\Config;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\RequestInterface;
use FedEx\CrossBorder\Model\ProductValidator;

class IndexBuilderPlugin
{
    /**
     * @var ProductValidator
     */
    protected $_productValidator;
    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * IndexBuilderPlugin constructor.
     *
     * @param Config $eavConfig
     * @param ProductValidator $productValidator
     */
    public function __construct(
        Config $eavConfig,
        ProductValidator $productValidator
    ) {
        $this->_productValidator = $productValidator;
        $this->_eavConfig = $eavConfig;
    }

    /**
     * Adds attribute into select and checks if value not null
     *
     * @param Select $select
     * @param $attributeCode
     * @return $this
     */
    protected function _addAttributeToSelect(Select $select, $attributeCode)
    {
        $attribute = $this->_eavConfig->getAttribute(Product::ENTITY, $attributeCode);
        if ($attribute->getId()) {
            $tableAlias = $attribute->getAttributeCode() . '_table';
            $entityIdField = $attribute->getEntityIdField();
            $stores = [0, $this->_productValidator->getHelper()->getStoreManager()->getStore()->getId()];
            $whereCondition = '';

            foreach ($stores as $storeId) {
                $alias = $tableAlias . (!$storeId ? '_def' : '');
                $condition = '`search_index`.`entity_id`  = `' . $alias . '`.`' . $entityIdField . '` AND ' .
                    '`' . $alias . '`.`attribute_id` = ' . $attribute->getId() . ' AND ' .
                    '`' . $alias . '`.`store_id` = ' . $storeId;

                $select->joinLeft(
                    [$alias  => $attribute->getBackendTable()],
                    $condition,
                    []
                );
            }

            $alias = 'IFNULL(`' . $tableAlias . '`.`value`, `' . $tableAlias . '_def`.`value`)';
            if ($attributeCode == ProductValidator::COO_ATTRIBUTE_CODE) {
                $whereCondition = $alias . ' IS NOT NULL';
            } elseif ($attributeCode == ProductValidator::HAZ_FLAG_ATTRIBUTE_CODE) {
                $whereCondition = $alias . ' = 0 OR ' .
                    $alias . ' IS NULL';
            } elseif ($attributeCode == ProductValidator::IMPORT_FLAG_ATTRIBUTE_CODE) {
                $whereCondition =  '((' .
                    $alias . ' NOT LIKE "' . $this->_productValidator->getHelper()->getSelectedCountry() . ',%" AND ' .
                    $alias . ' NOT LIKE "%,' . $this->_productValidator->getHelper()->getSelectedCountry() . ',%" AND ' .
                    $alias . ' NOT LIKE "%,' . $this->_productValidator->getHelper()->getSelectedCountry() . '" AND ' .
                    $alias . ' <> "' . $this->_productValidator->getHelper()->getSelectedCountry() . '"' .
                    ') OR ' . $alias . ' IS NULL)';
            }
            $select->where($whereCondition);
        }

        return $this;
    }

    /**
     * @param IndexBuilder $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return \Magento\Framework\DB\Select
     */
    public function aroundBuild(
        IndexBuilder $subject,
        callable $proceed,
        RequestInterface $request
    )
    {
        $select = $proceed($request);

        if ($this->_productValidator->getHelper()->isInternational()) {
            if ($this->_productValidator->isCooEnabled()) {
                $this->_addAttributeToSelect($select, ProductValidator::COO_ATTRIBUTE_CODE);
            }

            if ($this->_productValidator->isImportFlagEnabled()) {
                $this->_addAttributeToSelect($select, ProductValidator::IMPORT_FLAG_ATTRIBUTE_CODE);
            }

            if ($this->_productValidator->isHazFlagEnabled()) {
                $this->_addAttributeToSelect($select, ProductValidator::HAZ_FLAG_ATTRIBUTE_CODE);
            }
        }

        return $select;
    }
}