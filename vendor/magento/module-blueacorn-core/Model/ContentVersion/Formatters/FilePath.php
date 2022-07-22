<?php
/**
 * @package     BlueAcorn/Core
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
declare(strict_types=1);

namespace BlueAcorn\Core\Model\ContentVersion\Formatters;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

class FilePath
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(
        Reader $reader
    ) {
        $this->reader = $reader;
    }

    /**
     * Gets absolute file path to an installable resource
     *
     * @param string $file
     * @return string
     * @throws LocalizedException
     */
    public function format(string $file): string
    {
        list($module, $filePath) = \Magento\Framework\View\Asset\Repository::extractModule($file);

        if (!$module) {
            throw new LocalizedException(__('Invalid file format, please include module name in string, e.g. BlueAcorn_Core::file'));
        }

        return $this->reader->getModuleDir(
            Dir::MODULE_SETUP_DIR,
            $module
        ) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $filePath;
    }
}
