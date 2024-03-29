<?php
/**
 * ClassyLlama_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 Avalara, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace ClassyLlama\AvaTax\Model\Config\Source;

class TaxIncluded implements \Magento\Framework\Data\OptionSourceInterface
{
    const NET = 0;
    const GROSS = 1;
    
    /**
     * @return array
     */
    public function toSourceArray()
    {
        return [
            self::NET => __('Net'),
            self::GROSS => __('Gross')
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        $options = $this->toSourceArray();
        foreach ($options as $value=>$label) {
            $result[] = ['value' => $value, 'label' => $label];
        }
        return $result;
    }
}
