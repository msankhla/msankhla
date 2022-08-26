<?php
/**
 * FedEx Cross Border component
 *
 * @category    FedEx
 * @package     FedEx_CrossBorder
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\CrossBorder\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

class DimensionUnit implements ArrayInterface
{
    /**
     * Returns dimension units list
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'in', 'label' => __('in')],
            ['value' => 'cm', 'label' => __('cm')]
        ];
    }
}
