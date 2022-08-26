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
use FedEx\CrossBorder\Model\ResourceModel\QuoteLink;
use FedEx\CrossBorder\Model\ResourceModel\Scheduler;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Create geo ip table
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    protected function _createGeoIPTable($installer)
    {
        $tableName = $installer->getTable(GeoIP::TABLE_NAME);

        if (!$installer->getConnection()->isTableExists($tableName)) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )->addColumn(
                    'ip',
                    Table::TYPE_TEXT,
                    15,
                    ['nullable' => false, 'default' => ''],
                    'IP'
                )->addColumn(
                    'country_code',
                    Table::TYPE_TEXT,
                    3,
                    ['nullable' => false, 'default' => ''],
                    'Country Code'
                )->addColumn(
                    'country_currency',
                    Table::TYPE_TEXT,
                    3,
                    ['nullable' => false, 'default' => ''],
                    'Country Default Currency'
                )->addIndex(
                    $installer->getIdxName($tableName, ['ip'], AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['ip'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->setComment("Geo IP table");
            $installer->getConnection()->createTable($table);
        }

        return $this;
    }

    /**
     * Create available countries table
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    protected function _createAvailableCountriesTable($installer)
    {
        $tableName = $installer->getTable('fdxcb_available_countries');

        if (!$installer->getConnection()->isTableExists($tableName)) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )->addColumn(
                    'code',
                    Table::TYPE_TEXT,
                    3,
                    ['nullable' => false, 'default' => ''],
                    'Country Code'
                )->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    100,
                    ['nullable' => false, 'default' => ''],
                    'Country Name'
                )->addColumn(
                    'currency',
                    Table::TYPE_TEXT,
                    3,
                    ['nullable' => false, 'default' => ''],
                    'Country Default Currency'
                )->addIndex(
                    $installer->getIdxName($tableName, ['code'], AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['code'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->setComment("Available Countries Table");
            $installer->getConnection()->createTable($table);
        }

        return $this;
    }

    /**
     * Create quote table
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    protected function _createQuoteTable($installer)
    {
        $tableName = $installer->getTable(QuoteLink::TABLE_NAME);

        if (!$installer->getConnection()->isTableExists($tableName)) {
            $table = $installer->getConnection()
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
                    'fxcb_order_number',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => true, 'default' => ''],
                    'FedEx Order Number'
                )->addColumn(
                    'tracking_link',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'FedEx Tracking Link'
                )->addIndex(
                    $installer->getIdxName($tableName, ['quote_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['quote_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addIndex(
                    $installer->getIdxName($tableName, ['fxcb_order_number'], AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['fxcb_order_number'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addForeignKey(
                    $installer->getFkName(
                        QuoteLink::TABLE_NAME,
                        'quote_id',
                        'quote',
                        'entity_id'
                    ),
                    'quote_id',
                    $installer->getTable('quote'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )->setComment("FedEx Quote Table");
            $installer->getConnection()->createTable($table);
        }

        return $this;
    }

    /**
     * Create order table
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    protected function _createOrderTable($installer)
    {
        $tableName = $installer->getTable(OrderLink::TABLE_NAME);

        if (!$installer->getConnection()->isTableExists($tableName)) {
            $table = $installer->getConnection()
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
                    'fxcb_order_number',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => true, 'default' => ''],
                    'FedEx Order Number'
                )->addColumn(
                    'tracking_link',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'FedEx Tracking Link'
                )->addIndex(
                    $installer->getIdxName($tableName, ['order_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['order_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addIndex(
                    $installer->getIdxName($tableName, ['fxcb_order_number'], AdapterInterface::INDEX_TYPE_UNIQUE),
                    ['fxcb_order_number'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addForeignKey(
                    $installer->getFkName(
                        OrderLink::TABLE_NAME,
                        'order_id',
                        'sales_order',
                        'entity_id'
                    ),
                    'order_id',
                    $installer->getTable('sales_order'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )->setComment("FedEx Order Table");
            $installer->getConnection()->createTable($table);
        }

        return $this;
    }

    /**
     * Create scheduler table
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    protected function _createScheduler($installer)
    {
        $tableName = $installer->getTable(Scheduler::TABLE_NAME);

        if (!$installer->getConnection()->isTableExists($tableName)) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    32,
                    ['unsigned' => true, 'nullable' => ''],
                    'Type'
                )->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => true, 'default' => ''],
                    'Status'
                )->addColumn(
                    'json_data',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'JSON Data'
                )->addColumn(
                    'attempts',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'default' => 0],
                    'Attempts'
                )->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                )->addIndex(
                    $installer->getIdxName(
                        $tableName, ['status'],
                        AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['status'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )->addIndex(
                    $installer->getIdxName(
                        $tableName, ['created_at'],
                        AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['created_at'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )->addIndex(
                    $installer->getIdxName(
                        $tableName, ['updated_at'],
                        AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['updated_at'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )->setComment("FedEx Scheduler Table");
            $installer->getConnection()->createTable($table);
        }

        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $this->_createGeoIPTable(
            $installer
        )->_createAvailableCountriesTable(
            $installer
        )->_createQuoteTable(
            $installer
        )->_createOrderTable(
            $installer
        )->_createScheduler(
            $installer
        );
        $installer->endSetup();
    }
}