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
namespace FedEx\CrossBorder\Setup;

use Magento\Framework\Setup;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SampleData\Executor;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{
    const GROUP_FEDEX_CROSSBORDER   = 'FedEx Cross Border';

    /**
     * @var Executor
     */
    protected $_executor;

    /**
     * @var Installer
     */
    protected $_installer;

    /**
     * @var EavSetup
     */
    protected $_eavSetup;

    /**
     * InstallData constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param Executor $executor
     * @param Installer $installer
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Setup\SampleData\Executor $executor,
        Installer $installer
    ) {
        $this->_eavSetup = $eavSetupFactory->create();
        $this->_executor = $executor;
        $this->_installer = $installer;
    }

    /**
     * Create new attribute group for all attribute sets
     *
     * @param string $name
     * @param null $sortOrder
     * @return $this
     */
    public function createGroup($name, $sortOrder = null)
    {
        $entityTypeId = $this->_eavSetup->getEntityTypeId('catalog_product');
        $attributeSetIds = $this->_eavSetup->getAllAttributeSetIds($entityTypeId);

        foreach ($attributeSetIds as $attributeSetId) {
            if (!$this->_eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $name)) {
                $this->_eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $name, $sortOrder);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $moduleContext)
    {
        $this->createGroup(static::GROUP_FEDEX_CROSSBORDER);
        $this->_executor->exec($this->_installer);
    }
}