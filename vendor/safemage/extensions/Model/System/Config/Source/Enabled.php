<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/


namespace SafeMage\Extensions\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class provides option values for enabled/disabled dropdown in Stores->Configuration->SafeMage->Extensions.
 */
class Enabled implements ArrayInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Disabled')],
            ['value' => 1, 'label' => __('Enabled')]
        ];
    }
}
