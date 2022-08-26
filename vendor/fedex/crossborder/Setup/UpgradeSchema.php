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

use FedEx\CrossBorder\Model\ResourceModel\GeoIP;
use FedEx\CrossBorder\Model\ResourceModel\OrderLink;
use FedEx\CrossBorder\Model\ResourceModel\OrderLink\Address as OrderLinkAddress;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\Item as BoxItem;
use FedEx\CrossBorder\Model\ResourceModel\TaxRate;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Update method
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->_createPackNotificationTables(
                $setup
            )->_updateOrderTable(
                $setup
            );
        }

        if (version_compare($context->getVersion(), '1.0.1.2', '<')) {
            $this->_updateOrderLinkStatus(
                $setup
            );
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->_createTaxRateTable(
                $setup
            );
        }

        if (version_compare($context->getVersion(), '1.0.3.1', '<')) {
            $this->_createOrderLinkAddressTable(
                $setup
            );
        }

        if (version_compare($context->getVersion(), '1.0.5.2', '<')) {
            $this->_updateIpLength(
                $setup
            );
        }

        $setup->endSetup();
    }

    /**
     * Create order link address table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function _createOrderLinkAddressTable($setup)
    {
        $tableName = $setup->getTable(OrderLinkAddress::TABLE_NAME);

        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )->addColumn(
                    'order_link_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Order Link ID'
                )->addColumn(
                    'firstname',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'First Name'
                )->addColumn(
                    'lastname',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Last Name'
                )->addColumn(
                    'street',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Street'
                )->addColumn(
                    'city',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'City'
                )->addColumn(
                    'country_id',
                    Table::TYPE_TEXT,
                    3,
                    [],
                    'Country'
                )->addColumn(
                    'region',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Region'
                )->addColumn(
                    'postcode',
                    Table::TYPE_TEXT,
                    20,
                    [],
                    'Postcode'
                )->addColumn(
                    'telephone',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Phone'
                )->addColumn(
                    'fax',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Fax'
                )->addColumn(
                    'company',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Company'
                )->addIndex(
                    $setup->getIdxName($tableName, ['order_link_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['order_link_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addForeignKey(
                    $setup->getFkName(
                        OrderLinkAddress::TABLE_NAME,
                        'order_link_id',
                        OrderLink::TABLE_NAME,
                        'entity_id'
                    ),
                    'order_link_id',
                    $setup->getTable(OrderLink::TABLE_NAME),
                    'entity_id',
                    Table::ACTION_CASCADE
                )->setComment("Order Link Address Table");
            $setup->getConnection()->createTable($table);
        }

        return $this;
    }

    /**
     * Create pack notification tables
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function _createPackNotificationTables($setup)
    {
        $tableName = $setup->getTable(PackNotification::TABLE_NAME);

        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )->addColumn(
                    'order_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Order ID'
                )->addColumn(
                    'external_id',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'External ID'
                )->addColumn(
                    'retailer_pack_id',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'Retailer Pack ID'
                )->addColumn(
                    'tracking_number',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'Tracking Number'
                )->addColumn(
                    'dimension_unit',
                    Table::TYPE_TEXT,
                    2,
                    ['nullable' => true, 'default' => ''],
                    'Dimension Unit'
                )->addColumn(
                    'weight_unit',
                    Table::TYPE_TEXT,
                    2,
                    ['nullable' => true, 'default' => ''],
                    'Weight Unit'
                )->addColumn(
                    'document_url',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'Document Url'
                )->addColumn(
                    'cancel_url',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'Cancel Url'
                )->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    20,
                    ['nullable' => true, 'default' => ''],
                    'Cancel Url'
                )->addColumn(
                    'state',
                    Table::TYPE_TEXT,
                    12,
                    ['nullable' => true, 'default' => ''],
                    'Cancel Url'
                )->addForeignKey(
                    $setup->getFkName(
                        PackNotification::TABLE_NAME,
                        'order_id',
                        'sales_order',
                        'entity_id'
                    ),
                    'order_id',
                    $setup->getTable('sales_order'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )->setComment("FedEx Pack Notification Table");
            $setup->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable(Box::TABLE_NAME);

        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )->addColumn(
                    'pack_notification_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Pack Notification ID'
                )->addColumn(
                    'width',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => true],
                    'Width'
                )->addColumn(
                    'height',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => true],
                    'Height'
                )->addColumn(
                    'length',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => true],
                    'Length'
                )->addColumn(
                    'weight',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => true],
                    'Weight'
                )->addForeignKey(
                    $setup->getFkName(
                        Box::TABLE_NAME,
                        'pack_notification_id',
                        PackNotification::TABLE_NAME,
                        'entity_id'
                    ),
                    'pack_notification_id',
                    $setup->getTable(PackNotification::TABLE_NAME),
                    'entity_id',
                    Table::ACTION_CASCADE
                )->setComment("FedEx Pack Notification Box Table");
            $setup->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable(BoxItem::TABLE_NAME);

        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )->addColumn(
                    'box_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Box ID'
                )->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product ID'
                )->addColumn(
                    'order_item_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Order Item ID'
                )->addColumn(
                    'qty',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => true],
                    'Qty'
                )->addColumn(
                    'country_of_origin',
                    Table::TYPE_TEXT,
                    3,
                    ['nullable' => true],
                    'County of Origin'
                )->addForeignKey(
                    $setup->getFkName(
                        BoxItem::TABLE_NAME,
                        'box_id',
                        Box::TABLE_NAME,
                        'entity_id'
                    ),
                    'box_id',
                    $setup->getTable(Box::TABLE_NAME),
                    'entity_id',
                    Table::ACTION_CASCADE
                )->setComment("FedEx Pack Notification Box Item Table");
            $setup->getConnection()->createTable($table);
        }

        return $this;
    }

    /**
     * Create tax rate table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function _createTaxRateTable($setup)
    {
        $tableName = $setup->getTable(TaxRate::TABLE_NAME);

        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )->addColumn(
                    'quote_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Quote ID'
                )->addColumn(
                    'item_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Item ID'
                )->addColumn(
                    'shipping_method',
                    Table::TYPE_TEXT,
                    120,
                    [],
                    'Shipping Method'
                )->addColumn(
                    'tax_amount',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Tax Amount'
                )->addColumn(
                    'duty_amount',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Duty Amount'
                )->addIndex(
                    $setup->getIdxName(
                        $tableName, ['quote_id'],
                        AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['quote_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )->addIndex(
                    $setup->getIdxName(
                        $tableName, ['item_id'],
                        AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['item_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )->addIndex(
                    $setup->getIdxName($tableName, ['quote_id', 'item_id', 'shipping_method'], AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['quote_id', 'item_id', 'shipping_method'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addForeignKey(
                    $setup->getFkName(
                        TaxRate::TABLE_NAME,
                        'quote_id',
                        'quote',
                        'entity_id'
                    ),
                    'quote_id',
                    $setup->getTable('quote'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )->addForeignKey(
                    $setup->getFkName(
                        TaxRate::TABLE_NAME,
                        'item_id',
                        'quote_item',
                        'item_id'
                    ),
                    'item_id',
                    $setup->getTable('quote_item'),
                    'item_id',
                    Table::ACTION_CASCADE
                )->setComment("Tax Rate Table");
            $setup->getConnection()->createTable($table);
        }

        return $this;
    }

    /**
     * Update order table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function _updateOrderTable($setup)
    {
        $tableName = $setup->getTable(OrderLink::TABLE_NAME);

        if ($setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getTable($tableName);
            if (!$setup->getConnection()->tableColumnExists($tableName, 'status')) {
                $setup->getConnection()->addColumn(
                    $table,
                    'status',
                    [
                        'type'      => Table::TYPE_TEXT,
                        'length'    => 1,
                        'nullable'  => true,
                        'default'   => 'N',
                        'comment'   => 'FedEx Order Status',
                    ]
                );
            }
        }

        return $this;
    }

    /**
     * Update order link status
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function _updateOrderLinkStatus($setup)
    {
        $tableName = $setup->getTable(OrderLink::TABLE_NAME);

        if ($setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getTable($tableName);
            if ($setup->getConnection()->tableColumnExists($tableName, 'status')) {
                $setup->getConnection()->changeColumn(
                    $table,
                    'status',
                    'fxcb_status',
                    [
                        'type'      => Table::TYPE_TEXT,
                        'length'    => 1,
                        'nullable'  => true,
                        'default'   => 'N',
                        'comment'   => 'FedEx Order Status',
                    ]
                );
            }
        }

        return $this;
    }

    /**
     * Update ip length
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function _updateIpLength($setup)
    {
        $tableName = $setup->getTable(GeoIP::TABLE_NAME);

        if ($setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getTable($tableName);
            if ($setup->getConnection()->tableColumnExists($tableName, 'ip')) {
                $setup->getConnection()->changeColumn(
                    $table,
                    'ip',
                    'ip',
                    [
                        'type'      => Table::TYPE_TEXT,
                        'length'    => 39,
                        'nullable'  => false,
                        'default'   => '',
                        'comment'   => 'IP',
                    ]
                );
            }
        }

        return $this;
    }
}