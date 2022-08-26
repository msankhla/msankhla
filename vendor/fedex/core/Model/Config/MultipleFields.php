<?php
/**
 * FedEx Core component
 *
 * @category    FedEx
 * @package     FedEx_Core
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\Core\Model\Config;

use Magento\Framework\App\Config\Value as ConfigValue;

class MultipleFields extends ConfigValue
{
    protected $_isEnabled;

    /**
     * Checks if value is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (!isset($this->_isEnabled)) {
            $this->_isEnabled = true;
            $depends = $this->getDataByPath('field_config/depends/fields');
            if (is_array($depends)) {
                foreach ($depends as $item) {
                    $path = 'groups/' . $item['dependPath'][1] . '/fields/' . $item['dependPath'][2] . '/value';
                    if ($item['value'] != $this->getDataByPath($path)) {
                        $this->_isEnabled = false;
                        break;
                    }
                }
            }
        }

        return $this->_isEnabled;
    }
    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->isEnabled()) {
            unset($this->_data['value']['__empty']);
            $this->_data['value'] = json_encode($this->_data['value']);
        } else {
            $this->setValue(
                $this->getOldValue()
            );
        }

        return parent::beforeSave();
    }

    /**
     * Process data after load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->_data['value'] = json_decode($this->_data['value'], true);

        return parent::_afterLoad();
    }
}
