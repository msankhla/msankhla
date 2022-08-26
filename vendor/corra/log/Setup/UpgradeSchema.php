<?php

namespace Corra\Log\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 *
 * Corra\Log\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.1', '<')) {
            $this->addAlertTable($setup);
        }
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->alterMessageNAlertTable($setup);
        }
        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $this->alterAlertTableAddLogId($setup);
        }
        // TODO: define table indexes for frequent queries
    }

    /**
     * Adds an index to a table when the index is not exists.
     *
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    private function addAlertTable(SchemaSetupInterface $setup)
    {
        $installer = $setup;

        $table = $installer->getConnection()->newTable(
            $installer->getTable('corra_log_alert')
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
            'Alert Message'
        );
        $installer->getConnection()->createTable($table);
    }

    /**
     * Adds an index to a table when the index is not exists
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function alterMessageNAlertTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('corra_log'),
            'row_status',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Row status'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('corra_log_archive'),
            'row_status',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Row status'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('corra_log_alert'),
            'type',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Type'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('corra_log_alert'),
            'subtype',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Subtype'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function alterAlertTableAddLogId(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('corra_log_alert'),
            'log_id',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Log Id'
            ]
        );
    }
}
