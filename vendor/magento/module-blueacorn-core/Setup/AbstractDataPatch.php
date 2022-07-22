<?php
/**
 * @package     BlueAcorn/Core
 * @version     1.1.0
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
namespace BlueAcorn\Core\Setup;

use BlueAcorn\Core\Helper\Installs;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AbstractDataPatch
 * @package BlueAcorn\Core\Setup
 */
abstract class AbstractDataPatch implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var Reader */
    private $reader;

    /** @var Installs */
    private $installs;

    const MODULE_KEY = 'BlueAcorn_Core';

    /**
     * AbstractDataPatch constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Reader $reader
     * @param Installs $installs
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Reader $reader,
        Installs $installs
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->reader = $reader;
        $this->installs = $installs;
    }

    /**
     * @return DataPatchInterface|void
     * @throws \Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $dir = $this->reader->getModuleDir(
            Dir::MODULE_SETUP_DIR,
            $this::MODULE_KEY
        ) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'patches' . DIRECTORY_SEPARATOR;

        $blocks = $this->getBlocks();
        if (count($blocks) > 0) {
            $this->installs->processCmsBlocks($blocks, $dir);
        }

        $pages = $this->getPages();
        if (count($pages) > 0) {
            $this->installs->processCmsPages($pages, $dir);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array
     */
    protected function getBlocks(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getPages(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}