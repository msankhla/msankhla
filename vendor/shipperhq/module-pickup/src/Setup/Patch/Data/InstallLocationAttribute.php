<?php
/*
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Shipper
 * @copyright Copyright (c) 2022 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

declare(strict_types=1);

namespace ShipperHQ\Pickup\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

class InstallLocationAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * Category setup factory
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup, CategorySetupFactory $categorySetupFactory)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.1';
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Do Upgrade
     * @return void
     */
    public function apply()
    {
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        /* ------ shipperhq_shipping_group -------- */
        $catalogSetup->addAttribute(Product::ENTITY, 'shipperhq_location', [
            'type'                     => 'text',
            'backend'                  => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'input'                    => 'multiselect',
            'label'                    => 'Pickup Location',
            'global'                   => ScopedAttributeInterface::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'             => true,
            'used_in_product_listing'  => false
        ]);
        $entityTypeId = $catalogSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetArr = $catalogSetup->getAllAttributeSetIds($entityTypeId);
        $attributeId = $catalogSetup->getAttributeId($entityTypeId, 'shipperhq_location');
        foreach ($attributeSetArr as $attributeSetId) {
            $catalogSetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Shipping', '99');
            $attributeGroupId = $catalogSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Shipping');
            $catalogSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, '11');
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
