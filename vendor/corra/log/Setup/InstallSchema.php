<?php

namespace Corra\Log\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 *
 * Corra\Log\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        foreach (['corra_log', 'corra_log_archive'] as $tableName) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable($tableName)
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'type',
                Table::TYPE_TEXT,
                32,
                ['nullable' => true],
                'Log Type (for instance, Integration)'
            )->addColumn(
                'subtype',
                Table::TYPE_TEXT,
                32,
                ['nullable' => true],
                'Log Subtype (for instance, Order)'
            )->addColumn(
                'level',
                Table::TYPE_TEXT,
                9,
                ['nullable' => false],
                'Log level, aka Severity'
            )->addColumn(
                'message',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Message (JSON encoded)'
            )->addColumn(
                'log_id',
                Table::TYPE_TEXT,
                32,
                ['nullable' => true],
                'Unique identifier to identify a process'
            )->addColumn(
                'log_filename',
                Table::TYPE_TEXT,
                32,
                ['nullable' => true],
                'Log filename'
            );
            $installer->getConnection()->createTable($table);
        }
    }
}
