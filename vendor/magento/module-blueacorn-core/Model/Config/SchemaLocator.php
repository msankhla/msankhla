<?php
/**
 * @package     BlueAcorn/Core
 * @version     1.0.0
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
namespace BlueAcorn\Core\Model\Config;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

/**
 * Class SchemaLocator
 * @package BlueAcorn\Core\Model\Config
 */
class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * XML Schema definition
     */
    const CONFIG_FILE_SCHEMA = 'blueacorn_content.xsd';

    /**
     * @var string
     */
    protected $schema;

    /**
     * @var string
     */
    protected $perFileSchema;

    /**
     * SchemaLocator constructor.
     * @param Reader $moduleReader
     */
    public function __construct(Reader $moduleReader)
    {
        $configDir = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'BlueAcorn_Core');
        $this->schema = $configDir . DIRECTORY_SEPARATOR . self::CONFIG_FILE_SCHEMA;
        $this->perFileSchema = $configDir . DIRECTORY_SEPARATOR . self::CONFIG_FILE_SCHEMA;
    }

    /**
     * @inheritDoc
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @inheritDoc
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}
