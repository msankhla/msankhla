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

use FedEx\CrossBorder\Model\OrderStatusManagement;
use FedEx\CrossBorder\Model\ProductValidator;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SampleData\Executor;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var EavSetupFactory
     */
    protected $_eavSetupFactory;

    /**
     * @var Executor
     */
    protected $_executor;

    /**
     * @var Installer
     */
    protected $_installer;

    /**
     * @var StatusFactory
     */
    protected $_statusFactory;

    /**
     * @var StatusResourceFactory
     */
    protected $_statusResourceFactory;

    /**
     * UpgradeData constructor.
     *
     * @param AttributeFactory $attributeFactory
     * @param EavSetupFactory $eavSetupFactory
     * @param Executor $executor
     * @param Installer $installer
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        AttributeFactory $attributeFactory,
        EavSetupFactory $eavSetupFactory,
        Executor $executor,
        Installer $installer,
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->_attributeFactory = $attributeFactory;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_executor = $executor;
        $this->_installer = $installer;
        $this->_statusFactory = $statusFactory;
        $this->_statusResourceFactory = $statusResourceFactory;
    }

    /**
     * Update method
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->_executor->exec(
                $this->_installer
            );

            $this->_createOrderStatus(
                Order::STATE_PROCESSING,
                OrderStatusManagement::STATUS_READY_FOR_EXPORT,
                'Ready for Export'
            )->_importCOOValues(
                $setup
            );
        }

        if (version_compare($context->getVersion(), '1.0.1.1', '<')) {
            $this->_executor->exec(
                $this->_installer
            );

            $this->_createOrderStatus(
                Order::STATE_PROCESSING,
                OrderStatusManagement::STATUS_CANCELLATION_REQUEST,
                'Cancellation Request'
            );
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->_executor->exec(
                $this->_installer
            );
        }

        if (version_compare($context->getVersion(), '1.0.4.6', '<')) {
            $this->_removeConfigData(
                $setup,
                'fedex_crossborder/product_validation/%'
            );
        }

        $setup->endSetup();
    }

    /**
     * Create new order status
     *
     * @param string $state
     * @param string $status
     * @param string $label
     * @return $this
     */
    protected function _createOrderStatus($state, $status, $label)
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->_statusResourceFactory->create();
        /** @var Status $statusModel */
        $statusModel = $this->_statusFactory->create();
        $statusModel->setData([
            'status'    => $status,
            'label'     => $label,
        ]);
        $statusResource->save($statusModel);
        $statusModel->assignState($state, false, true);

        return $this;
    }

    /**
     * Import COO values from attribute Country of Manufacture
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    protected function _importCOOValues($setup)
    {
        /** @var Attribute $attribute */
        $attribute = $this->_attributeFactory->create();
        $attribute->loadByCode('catalog_product', 'country_of_manufacture');

        /** @var Attribute $coo */
        $coo = $this->_attributeFactory->create();
        $coo->loadByCode('catalog_product', ProductValidator::COO_ATTRIBUTE_CODE);
        if ($attribute && $attribute->getId() &&
            $coo && $coo->getId()
        ) {
            $select = $setup->getConnection()->select(
            )->from(
                $attribute->getBackendTable(),
                [
                    new \Zend_Db_Expr($coo->getId()),
                    'store_id',
                    $attribute->getEntityIdField(),
                    'value'
                ]
            )->where(
                'attribute_id = ?',
                $attribute->getId()
            );

            $select = $setup->getConnection()->insertFromSelect(
                $select,
                $coo->getBackendTable(),
                [
                    'attribute_id',
                    'store_id',
                    $attribute->getEntityIdField(),
                    'value'
                ],
                AdapterInterface::REPLACE
            );

            $setup->getConnection()->query($select);
        }

        return $this;
    }

    /**
     * Remove config data
     *
     * @param ModuleDataSetupInterface $setup
     * @param array|string|null $list
     * @return $this
     */
    protected function _removeConfigData($setup, $list = null)
    {
        if (is_string($list)) {
            $list = [$list];
        }
        if (is_array($list) && !empty($list)) {
            $configTable = $setup->getTable('core_config_data');
            foreach ($list as $path) {
                $setup->getConnection()->delete(
                    $configTable,
                    sprintf(
                        '`path` like "%s"',
                        $path
                    )
                );
            }
        }

        return $this;
    }
}
