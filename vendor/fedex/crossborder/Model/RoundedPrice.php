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
namespace FedEx\CrossBorder\Model;

use FedEx\CrossBorder\Helper\Data as Helper;

class RoundedPrice
{
    const CONFIG_PATH   = 'fedex_crossborder/rounded_price/';

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * WelcomeMat constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Returns rounded price country list
     *
     * @return array
     */
    public function getCountries()
    {
        return explode(',', $this->getHelper()->getConfig(static::CONFIG_PATH . 'specificcountry', ''));
    }

    /**
     * @return Helper
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Checks if rounded price enabled for all countries
     *
     * @return bool
     */
    public function isAllCountries()
    {
        return (bool) !$this->getHelper()->getConfig(static::CONFIG_PATH . 'allowspecific', 0);
    }

    /**
     * Checks if welcome mat enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->getHelper()->isEnabled() && $this->getHelper()->getConfig(static::CONFIG_PATH . 'enable', 0);
    }
}