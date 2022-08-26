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
namespace FedEx\CrossBorder\Model\Config;

use FedEx\Core\Model\Config\MultipleFields;

class CountryCmsBlock extends MultipleFields
{
    /**
     * Removes duplicates
     *
     * @param string $fieldName
     * @return $this
     */
    protected function _removeDuplicates($fieldName)
    {
        $list = [];

        foreach ($this->getValue() as $_key => $_data) {
            if (isset($_data[$fieldName])) {
                $_value = $_data[$fieldName];
                if (isset($list[$_value])) {
                    unset($this->_data['value'][$list[$_value]]);
                }

                $list[$_value] = $_key;
            }
        }

        return $this;
    }

    /**
     * Sorting function
     *
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    protected function _sort($a, $b)
    {
        return (
        !isset($a['country']) || !isset($b['country']) || $a['country'] == $b['country'] ?
            0 :
            ($a['country'] < $b['country'] ? -1 : 1)
        );
    }

    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        uasort($this->_data['value'], [$this, '_sort']);
        if ($this->isEnabled()) {
            $this->_removeDuplicates('country');
        }

        return parent::beforeSave();
    }
}
