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
namespace FedEx\CrossBorder\Ui\Component\Order\Listing\Column\Status;

use Magento\Framework\Data\OptionSourceInterface;

class Options  implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * @var array
     */
    protected $_statusList  = [
        'C' => 'Cancelled',
        'W' => 'Classification Information Provided',
        'Q' => 'Consumer inquiry',
        'D' => 'Delivered',
        'I' => 'Distribution Center	',
        'N' => 'New',
        'P' => 'Processing',
        'Z' => 'Ready for Export',
        'X' => 'RPS',
        'S' => 'Shipped',
        'V' => 'Vended ',
    ];

    /**
     * Returns option label
     *
     * @param string $value
     * @return string
     */
    public function getLabel($value)
    {
        return (isset($this->_statusList[$value]) ? $this->_statusList[$value] : 'Unknown');
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!isset($this->_options)) {
            $this->_options = [];

            foreach ($this->_statusList as $value => $label) {
                $this->_options[] = [
                    'value' => $value,
                    'label' => $label,
                ];
            }
        }

        return $this->_options;
    }
}