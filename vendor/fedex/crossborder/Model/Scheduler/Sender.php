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
namespace FedEx\CrossBorder\Model\Scheduler;

use FedEx\CrossBorder\Model\AbstractImport;

class Sender extends AbstractImport
{
    const LOG_FILE = 'FedEx/CrossBorder/Scheduler.log';

    /**
     * @var string
     */
    protected $_type;

    /**
     * Returns endpoint value
     *
     * @return string
     */
    public function getEndpoint()
    {
        $value = $this->getConfig(self::CONFIG_PATH . $this->getType() . '_update_path');
        return (!empty($value) ? '/' . $value : '');
    }

    /**
     * Returns type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Sets type
     *
     * @param string $value
     * @return $this
     */
    public function setType($value)
    {
        $this->_type = $value;

        return $this;
    }
}