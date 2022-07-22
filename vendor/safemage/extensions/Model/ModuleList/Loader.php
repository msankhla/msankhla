<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/


namespace SafeMage\Extensions\Model\ModuleList;

use Magento\Framework\Xml\Parser;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Loader of module list information from the filesystem.
 */
class Loader
{
    /**
     * @var \SafeMage\Extensions\Model\ModuleList\Converter
     */
    private $converter;

    /**
     * @var \Magento\Framework\Xml\Parser
     */
    private $parser;

    /**
     * @var ComponentRegistrarInterface
     */
    private $moduleRegistry;

    /**
     * @var DriverInterface
     */
    private $filesystemDriver;

    /**
     * @param Parser $parser
     * @param ComponentRegistrarInterface $moduleRegistry
     * @param \Magento\Framework\Filesystem\Driver\File $filesystemDriver
     * @param \SafeMage\Extensions\Model\ModuleList\Converter $converter
     */
    public function __construct(
        Parser $parser,
        ComponentRegistrarInterface $moduleRegistry,
        \Magento\Framework\Filesystem\Driver\File $filesystemDriver,
        \SafeMage\Extensions\Model\ModuleList\Converter $converter
    ) {
        $this->converter = $converter;
        $this->parser = $parser;
        $this->parser->initErrorHandler();
        $this->moduleRegistry = $moduleRegistry;
        $this->filesystemDriver = $filesystemDriver;
    }

    /**
     * Loads the full module list information. Excludes modules specified in $exclude.
     *
     * @param array $exclude
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    public function load(array $exclude = [])
    {
        $result = [];
        foreach ($this->getModuleConfigs() as list($file, $contents)) {
            try {
                $this->parser->loadXML($contents);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase(
                        'Invalid Document: %1%2 Error: %3',
                        [$file, PHP_EOL, $e->getMessage()]
                    ),
                    $e
                );
            }

            $data = $this->converter->convert($this->parser->getDom());
            $name = key($data);
            if (!in_array($name, $exclude)) {
                $result[$name] = $data[$name];
            }
        }
        return $this->sortBySequence($result);
    }

    /**
     * Retrieves contents of module.xml file.
     *
     * @return \Traversable
     */
    private function getModuleConfigs()
    {
        $modulePaths = $this->moduleRegistry->getPaths(ComponentRegistrar::MODULE);
        foreach ($modulePaths as $key => $modulePath) {
            if (strpos($key, 'SafeMage') !== false) {
                $filePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, "$modulePath/etc/module.xml");
                yield [$filePath, $this->filesystemDriver->fileGetContents($filePath)];
            }
        }
    }

    /**
     * Sort the list of modules using "sequence" key in meta-information.
     *
     * @param array $origList
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function sortBySequence($origList)
    {
        ksort($origList);
        $expanded = [];
        foreach ($origList as $moduleName => $value) {
            $expanded[] = [
                'name' => $moduleName,
                'sequence' => $this->expandSequence($origList, $moduleName),
            ];
        }

        // Use "bubble sorting" because usort does not check each pair of elements and in this case it is important
        $total = count($expanded);
        for ($i = 0; $i < $total - 1; $i++) {
            for ($j = $i; $j < $total; $j++) {
                if (in_array($expanded[$j]['name'], $expanded[$i]['sequence'])) {
                    $temp = $expanded[$i];
                    $expanded[$i] = $expanded[$j];
                    $expanded[$j] = $temp;
                }
            }
        }

        $result = [];
        foreach ($expanded as $pair) {
            $result[$pair['name']] = $origList[$pair['name']];
        }

        return $result;
    }

    /**
     * Accumulate information about all transitive "sequence" references.
     *
     * @param array $list
     * @param string $name
     * @param array $accumulated
     * @return array
     * @throws \Exception
     */
    private function expandSequence($list, $name, $accumulated = [])
    {
        $accumulated[] = $name;
        $result = $list[$name]['sequence'];
        foreach ($result as $relatedName) {
            if (in_array($relatedName, $accumulated)) {
                throw new \Exception("Circular sequence reference from '{$name}' to '{$relatedName}'.");
            }
            if (!isset($list[$relatedName])) {
                continue;
            }
            $relatedResult = $this->expandSequence($list, $relatedName, $accumulated);
            $result = array_unique(array_merge($result, $relatedResult));
        }
        return $result;
    }
}
