<?php

namespace Corra\Log\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Monolog\Logger;

/**
 * Class LogLevels
 *
 * Corra\Log\Model\Config\Source
 */
class LogLevels implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $levelsArray = Logger::getLevels();
        $result = [];
        foreach ($levelsArray as $levelName => $levelValue) {
            $result[] = [
                'value' => $levelValue,
                'label' => $levelName
            ];
        }

        return $result;
    }
}
