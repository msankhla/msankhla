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

class MerchantControl
{
    const CONFIG_PATH   = 'fedex_crossborder/merchant_control/';

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * MerchantControl constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Checks if Monitor App can be shown
     *
     * @return bool
     */
    public function canShowMonitorApp()
    {
        return (bool) $this->isEnabled() && $this->_helper->getConfig(static::CONFIG_PATH . 'show_monitor_app');
    }

    /**
     * Checks if tracking link can be shown
     *
     * @return bool
     */
    public function canShowTrackingLink()
    {
        return (bool) $this->isEnabled() && $this->_helper->getConfig(static::CONFIG_PATH . 'show_tracking_link');
    }

    /**
     * Checks if custom shipping rates should be used
     *
     * @return bool
     */
    public function customShippingRates()
    {
        return (bool) $this->isEnabled() && $this->_helper->getConfig(static::CONFIG_PATH . 'custom_shipping_rates');
    }

    /**
     * Returns account type
     *
     * @return string
     */
    public function getAccountType()
    {
        return $this->_helper->getConfig(static::CONFIG_PATH . 'account_type');
    }

    /**
     * @return Helper
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Returns subsidize shipping amount
     *
     * @return float
     */
    public function getSubsidizeShippingAmount()
    {
        return floatval($this->_helper->getConfig(static::CONFIG_PATH . 'subsidize_shipping_amount', 0));
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
