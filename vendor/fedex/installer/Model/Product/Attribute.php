<?php
/**
 * FedEx Installer component
 *
 * @category    FedEx
 * @package     FedEx_Installer
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\Installer\Model\Product;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Framework\Setup\SampleData\FixtureManager;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Store\Model\StoreManagerInterface;

class Attribute
{
    const GROUP_GENERAL = 'General';

    /**
     * @var AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var SetFactory
     */
    protected $_attributeSetFactory;

    /**
     * @var CollectionFactory
     */
    protected $_attrOptionCollectionFactory;

    /**
     * @var Csv
     */
    protected $_csvReader;

    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var EavSetup
     */
    protected $_eavSetup;

    /**
     * @var int
     */
    protected $_entityTypeId;

    /**
     * @var FixtureManager
     */
    protected $_fixtureManager;

    /**
     * @var ProductHelper
     */
    protected $_productHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Attribute constructor.
     *
     * @param AttributeFactory $attributeFactory
     * @param Config $eavConfig
     * @param CollectionFactory $attrOptionCollectionFactory
     * @param ProductHelper $productHelper
     * @param SampleDataContext $sampleDataContext
     * @param SetFactory $attributeSetFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        AttributeFactory $attributeFactory,
        Config $eavConfig,
        CollectionFactory $attrOptionCollectionFactory,
        EavSetupFactory $eavSetupFactory,
        ProductHelper $productHelper,
        SampleDataContext $sampleDataContext,
        SetFactory $attributeSetFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->_attributeFactory = $attributeFactory;
        $this->_attributeSetFactory = $attributeSetFactory;
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->_csvReader = $sampleDataContext->getCsvReader();
        $this->_eavConfig = $eavConfig;
        $this->_eavSetup = $eavSetupFactory->create();
        $this->_fixtureManager = $sampleDataContext->getFixtureManager();
        $this->_productHelper = $productHelper;
        $this->_storeManager = $storeManager;
    }

    /**
     * Adds attribute set if not exist
     *
     * @param string $name
     * @return int
     */
    protected function _addAttributeSet($name)
    {
        $id = $this->_eavSetup->getAttributeSet($this->_getEntityTypeId(), $name, 'attribute_set_id');
        if (!$id) {
            $defaultSetId = $this->_eavConfig->getEntityType(
                \Magento\Catalog\Model\Product::ENTITY
            )->getDefaultAttributeSetId();

            $id = $this->_attributeSetFactory->create(
            )->setEntityTypeId(
                $this->_getEntityTypeId()
            )->setAttributeSetName(
                $name
            )->save()->initFromSkeleton(
                $defaultSetId
            )->save()->getId();
        }

        return $id;
    }

    /**
     * Converting attribute options from csv to correct sql values
     *
     * @param array $values
     * @return array
     */
    protected function _convertOption($values)
    {
        $result = ['order' => [], 'value' => []];
        $i = 0;
        foreach ($values as $value) {
            $result['order']['option_' . $i] = (string)$i;
            $result['value']['option_' . $i] = [0 => $value, 1 => ''];
            $i++;
        }

        return $result;
    }

    /**
     * Returns product entity type id
     *
     * @return int|mixed
     */
    protected function _getEntityTypeId()
    {
        if (!$this->_entityTypeId) {
            $this->_entityTypeId = $this->_eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
        }
        return $this->_entityTypeId;
    }

    /**
     * Returns frontend_label
     *
     * @param string $frontendLabel
     * @return array
     */
    protected function _getFrontendLabel($frontendLabel)
    {
        $array = explode("\n", $frontendLabel);
        if (count($array) > 1) {
            $frontendLabel = [
                \Magento\Store\Model\Store::DEFAULT_STORE_ID                => $array[0],
                $this->_storeManager->getDefaultStoreView()->getStoreId()   => $array[1]
            ];
        }

        return $frontendLabel;
    }

    /**
     * Returns options
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param array $data
     * @return array
     */
    protected function _getOption($attribute, $data)
    {
        $result = [];
        if (!empty($data['option'])) {
            $data['option'] = explode("\n", $data['option']);
            /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $options */
            $options = $this->_attrOptionCollectionFactory->create(
            )->setAttributeFilter(
                $attribute->getId()
            )->setPositionOrder(
                'asc',
                true
            )->load();

            foreach ($data['option'] as $value) {
                $value = trim($value);
                if (!$options->getItemByColumnValue('value', $value)) {
                    $result[] = $value;
                }
            }

            if (count($result)) {
                $result = $this->_convertOption($result);
            }
        }


        return $result;
    }

    /**
     * Installing process
     *
     * @param array $fixtures
     */
    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileName) {
            $fileName = $this->_fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->_csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $attributeSets = [];
                $data = [];
                $group = '';
                $sortOrder = null;
                foreach ($row as $key => $value) {
                    $value = trim($value);
                    switch ($header[$key]) {
                        case 'group':
                            if (!empty($value)) {
                                $group = $value;
                            }
                            break;
                        case 'attribute_set':
                            if (!empty($value)) {
                                $attributeSets = explode("\n", $value);
                            }

                            break;
                        case 'label':
                            $data[$header[$key]] = $this->_getFrontendLabel($value);
                            break;
                        case 'sort_order':
                            $value = intval($value);
                            if ($value > 0) {
                                $sortOrder = $value;
                            }
                            break;
                        default:
                            $data[$header[$key]] = $value;
                    }
                }

                /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                $attribute = $this->_eavConfig->getAttribute('catalog_product', $data['attribute_code']);
                if (!$attribute) {
                    $attribute = $this->_attributeFactory->create();
                }
                $data['option'] = $this->_getOption($attribute, $data);
                if (empty($data['source'])) {
                    $data['source'] = $this->_productHelper->getAttributeSourceModelByInputType($data['input']);
                }
                if (empty($data['backend'])) {
                    $data['backend'] = $this->_productHelper->getAttributeBackendModelByInputType($data['input']);
                }
                if (empty($data['type'])) {
                    $data['type'] = $attribute->getBackendTypeByInput($data['input']);
                }

                if (empty($attributeSets) && !empty($group)) {
                    $data['group'] = $group;
                }

                $this->_eavSetup->addAttribute(
                    $this->_getEntityTypeId(),
                    $data['attribute_code'],
                    $data
                );

                if (!empty($attributeSets)) {
                    foreach ($attributeSets as $setName) {
                        if (empty($setName)) {
                            continue;
                        }

                        $setId = $this->_addAttributeSet($setName);
                        $this->_eavSetup->addAttributeGroup(
                            $this->_getEntityTypeId(),
                            $setId,
                            $group
                        )->addAttributeToSet(
                            $this->_getEntityTypeId(),
                            $setId,
                            $group,
                            $data['attribute_code'],
                            $sortOrder
                        );
                    }
                }
            }
        }
        $this->_eavConfig->clear();
    }
}
