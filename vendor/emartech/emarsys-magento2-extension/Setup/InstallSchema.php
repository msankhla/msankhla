<?php

namespace Emartech\Emarsys\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     * @throws \Zend_Db_Exception
     */
    // @codingStandardsIgnoreLine
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $this->createEmarsysEventsTable($setup);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    private function createEmarsysEventsTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('emarsys_events_data');
        if ($setup->getConnection()->isTableExists($tableName) != true) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('emarsys_events_data')
            )
                ->addColumn(
                    'event_id',
                    Table::TYPE_BIGINT,
                    null,
                    [
                        'identity' => true, 'unsigned' => true,
                        'nullable' => false, 'primary' => true,
                    ],
                    'Event Id'
                )
                ->addColumn(
                    'website_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['default' => null, 'nullable' => true],
                    'Website ID'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['default' => null, 'nullable' => true],
                    'Store ID'
                )->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Entity ID'
                )
                ->addColumn(
                    'event_type',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null, 'nullable' => false],
                    'Event Type'
                )
                ->addColumn(
                    'event_data',
                    Table::TYPE_BLOB,
                    null,
                    ['default' => null, 'nullable' => false],
                    'Event Data'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'default'  => Table::TIMESTAMP_INIT,
                        'nullable' => false,
                    ],
                    'Timestamp'
                )
                ->addIndex(
                    $setup->getIdxName(
                        $setup->getTable('emarsys_events_data'),
                        ['event_type'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['event_type'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->addIndex(
                    $setup->getIdxName(
                        $setup->getTable('emarsys_events_data'),
                        ['created_at'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['created_at'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
                );
            $setup->getConnection()->createTable($table);
            $setup->getConnection()->modifyColumn(
                $tableName,
                'event_data',
                'mediumblob'
            );
        }
    }
}
