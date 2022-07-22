<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/


namespace SafeMage\Extensions\Model\ModuleList;

/**
 * Copied that class from Magento as default converter only parses "name", "setup_version" and "sequence" nodes,
 * but we need to parse custom fields from module.xml.
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function convert($source)
    {
        $modules = [];
        $xpath = new \DOMXPath($source);
        /** @var $moduleNode \DOMNode */
        foreach ($xpath->query('/config/module') as $moduleNode) {
            $moduleData = [];
            $moduleAttributes = $moduleNode->attributes;
            $nameNode = $moduleAttributes->getNamedItem('name');
            if ($nameNode === null) {
                throw new \Exception('Attribute "name" is required for module node.');
            }
            $moduleData['name'] = $nameNode->nodeValue;
            $name = $moduleData['name'];
            $versionNode = $moduleAttributes->getNamedItem('setup_version');
            if ($versionNode === null) {
                throw new \Exception("Attribute 'setup_version' is missing for module '{$name}'.");
            }
            $moduleData['setup_version'] = $versionNode->nodeValue;
            $moduleData['sequence'] = [];
            /** @var $childNode \DOMNode */
            foreach ($moduleNode->childNodes as $childNode) {
                switch ($childNode->nodeName) {
                    case 'sequence':
                        $moduleData['sequence'] = $this->_readModules($childNode);
                        break;
                    case 'module_name':
                        $moduleData['module_name'] = $childNode->nodeValue ? $childNode->nodeValue : '';
                        break;
                    case 'cache_key':
                        $moduleData['cache_key'] = $childNode->nodeValue ? $childNode->nodeValue : '';
                        break;
                    case 'url':
                        $moduleData['url'] = $childNode->nodeValue ? $childNode->nodeValue : '';
                        break;
                }
            }
            // Use module name as a key in the result array to allow quick access to module configuration
            $modules[$nameNode->nodeValue] = $moduleData;
        }
        return $modules;
    }

    /**
     * Convert module depends node into assoc array.
     *
     * @param \DOMNode $node
     * @return array
     * @throws \Exception
     */
    protected function _readModules(\DOMNode $node)
    {
        $result = [];
        /** @var $childNode \DOMNode */
        foreach ($node->childNodes as $childNode) {
            switch ($childNode->nodeName) {
                case 'module':
                    $nameNode = $childNode->attributes->getNamedItem('name');
                    if ($nameNode === null) {
                        throw new \Exception('Attribute "name" is required for module node.');
                    }
                    $result[] = $nameNode->nodeValue;
                    break;
            }
        }
        return $result;
    }
}
