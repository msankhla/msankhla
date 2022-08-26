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

class DomesticShipping implements ArrayInterface
{
    /**
     * Returns carriers options
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            0   => __('Fixed price'),
            1   => __('Specific Shipping Method'),
        ];
    }

    /**
     * Returns carriers list
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->getOptions() as $value => $label)
        {
            $result[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $result;
    }
}
