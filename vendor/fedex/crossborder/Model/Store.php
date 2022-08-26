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

class Store extends \Magento\Store\Model\Store
{
    const XML_PATH_DEFAULT_COUNTRY  = 'general/country/default';
    const XML_PATH_GEOIP_ENABLED    = 'fedex_crossborder/geo_ip/enable';

    const CONTEXT_COUNTRY           = 'current_country';
    const FIELD_COUNTRY             = 'current_country_code';

    /**
     * Returns default country code
     *
     * @return string
     */
    public function getDefaultCountryCode()
    {
        return (string) $this->getConfig(static::XML_PATH_DEFAULT_COUNTRY);
    }

    /**
     * Returns current country code
     *
     * @return string
     */
    public function getCurrentCountryCode()
    {
        return $code = $this->_httpContext->getValue(static::CONTEXT_COUNTRY) ?? $this->_getSession()->getData(static::FIELD_COUNTRY);
    }

    /**
     * Sets current country code
     *
     * @param string $code
     * @return $this
     */
    public function setCurrentCountryCode($code)
    {
        $code = strtoupper($code);
        if (!empty($code)) {
            $this->_getSession()->setData(
                static::FIELD_COUNTRY,
                $code
            );

            $this->_httpContext->setValue(
                static::CONTEXT_COUNTRY,
                $code,
                $this->getDefaultCountryCode()
            );
        } elseif (empty($code)) {
            $this->_getSession()->unsetData(static::FIELD_COUNTRY);
        }

        return $this;
    }
}