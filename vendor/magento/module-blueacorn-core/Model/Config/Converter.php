<?php
/**
 * @package     BlueAcorn/Core
 * @version     1.0.0
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
namespace BlueAcorn\Core\Model\Config;

use Magento\Framework\Config\ConverterInterface;

class Converter implements ConverterInterface
{
    /**
     * @var ConverterInterface[]
     */
    private $converters;

    /**
     * @param ConverterInterface[] $converters
     */
    public function __construct(array $converters = [])
    {
        $this->converters = $converters;
    }

    /**
     * @param \DOMDocument $source
     * @return array|void
     */
    public function convert($source)
    {
        $data = [];

        foreach ($this->converters as $converter) {
            $data = array_merge_recursive($data, $converter->convert($source));
        }

        return $data;
    }
}
