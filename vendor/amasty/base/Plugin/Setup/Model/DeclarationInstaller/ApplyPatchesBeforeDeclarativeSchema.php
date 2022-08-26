<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Magento 2 Base Package
*/

declare(strict_types=1);

namespace Amasty\Base\Plugin\Setup\Model\DeclarationInstaller;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Db\SchemaBuilderFactory;
use Magento\Framework\Setup\Declaration\Schema\Diff\DiffInterface;
use Magento\Framework\Setup\Declaration\Schema\Diff\SchemaDiff;
use Magento\Framework\Setup\Declaration\Schema\DryRunLogger;
use Magento\Framework\Setup\Declaration\Schema\Dto\SchemaFactory;
use Magento\Framework\Setup\Declaration\Schema\OperationsExecutor;
use Magento\Framework\Setup\Declaration\Schema\SchemaConfigFactory;
use Magento\Framework\Setup\Patch\PatchApplier;
use Magento\Framework\Setup\Patch\PatchHistory;

class ApplyPatchesBeforeDeclarativeSchema
{
    /**
     * @var PatchApplier
     */
    private $patchApplier;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string[]
     */
    private $moduleNames;

    /**
     * @var SchemaDiff
     */
    private $schemaDiff;

    /**
     * @var SchemaConfigFactory
     */
    private $schemaConfigFactory;

    /**
     * @var SchemaFactory
     */
    private $schemaFactory;

    /**
     * @var SchemaBuilderFactory
     */
    private $dbSchemaBuilderFactory;

    public function __construct(
        PatchApplier $patchApplier,
        ResourceConnection $resourceConnection,
        SchemaConfigFactory $schemaConfigFactory,
        SchemaDiff $schemaDiff,
        SchemaFactory $schemaFactory,
        SchemaBuilderFactory $dbSchemaBuilderFactory,
        array $moduleNames = []
    ) {
        $this->patchApplier = $patchApplier;
        $this->resourceConnection = $resourceConnection;
        $this->moduleNames = $moduleNames;
        $this->schemaConfigFactory = $schemaConfigFactory;
        $this->schemaDiff = $schemaDiff;
        $this->schemaFactory = $schemaFactory;
        $this->dbSchemaBuilderFactory = $dbSchemaBuilderFactory;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @see OperationsExecutor::execute()
     */
    public function beforeExecute(
        OperationsExecutor $operationsExecutor,
        DiffInterface $diff,
        array $requestData
    ): array {
        $isDryRun = $requestData[DryRunLogger::INPUT_KEY_DRY_RUN_MODE] ?? true;
        $connection = $this->resourceConnection->getConnection();

        if (!$isDryRun
            && $connection->isTableExists($this->resourceConnection->getTableName(PatchHistory::TABLE_NAME))
        ) {
            $patchesApplied = false;

            foreach ($this->moduleNames as $moduleName) {
                $this->patchApplier->applySchemaPatch($moduleName);
                $patchesApplied = true;
            }

            if ($patchesApplied) {
                $schemaConfig = $this->schemaConfigFactory->create();
                $schema = $this->schemaFactory->create();
                $dbSchemaBuilder = $this->dbSchemaBuilderFactory->create();
                $declarativeSchema = $schemaConfig->getDeclarationConfig();
                $dbSchema = $dbSchemaBuilder->build($schema);
                $diff = $this->schemaDiff->diff($declarativeSchema, $dbSchema);
            }
        }

        return [$diff, $requestData];
    }
}
