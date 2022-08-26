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
namespace FedEx\CrossBorder\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use FedEx\Core\Helper\AbstractHelper;
use FedEx\CrossBorder\Model\Config\Source\AvailableCountries;
use FedEx\CrossBorder\Model\GeoIP;
use FedEx\CrossBorder\Model\GeoIPFactory;

class Data extends AbstractHelper
{
    const CONFIG_PATH_GENERAL       = 'fedex_crossborder/general/';

    /**
     * @var AvailableCountries
     */
    protected $_availableCountries;

    /**
     * @var GeoIP
     */
    protected $_geoIP;

    /**
     * @var GeoIPFactory
     */
    protected $_geoIPFactory;

    /**
     * @var string
     */
    protected $_orderConfirmationUrl;

    /**
     * @var string
     */
    protected $_selectedCountry;

    /**
     * @var string
     */
    protected $_selectedCurrency;

    /**
     * @var SessionManagerInterface
     */
    protected $_session;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var bool
     */
    protected $_isDomesticShipping  = false;

    /**
     * Data constructor.
     *
     * @param AvailableCountries $availableCountries
     * @param GeoIPFactory $geoIPFactory
     * @param SessionManagerInterface $sessionManager
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     */
    public function __construct(
        AvailableCountries $availableCountries,
        GeoIPFactory $geoIPFactory,
        SessionManagerInterface $sessionManager,
        StoreManagerInterface $storeManager,
        Context $context
    ) {
        $this->_availableCountries = $availableCountries;
        $this->_geoIPFactory = $geoIPFactory;
        $this->_session = $sessionManager;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Checks if domestic shipping flag enabled
     *
     * @param bool|null $value
     * @return bool
     */
    public function isDomesticShipping($value = null)
    {
        if (isset($value)) {
            $this->_isDomesticShipping = $value;
        }

        return $this->_isDomesticShipping;
    }

    /**
     * Checks if module enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->getConfig(static::CONFIG_PATH_GENERAL . 'enable', 0);
    }

    /**
     * Checks if selected international shipping
     *
     * @return bool
     */
    public function isInternational()
    {
        return (bool) $this->isEnabled() && $this->getSelectedCountry() != $this->getDefaultCountry();
    }

    /**
     * Checks if logs enabled
     *
     * @return bool
     */
    public function isLogsEnabled()
    {
        return (bool) $this->getConfig(static::CONFIG_PATH_GENERAL . 'logs', 0);
    }

    /**
     * Checks if order confirmation email should be send
     *
     * @return bool
     */
    public function isOrderConfirmationEmail()
    {
        return (bool) $this->getConfig(static::CONFIG_PATH_GENERAL . 'order_confirmation_email', 0);
    }

    /**
     * Returns API client id value
     *
     * @return string
     */
    public function getApiClientId()
    {
        return (string) $this->getConfig(static::CONFIG_PATH_GENERAL . 'api_client_id');
    }

    /**
     * Returns API client secret value
     *
     * @return string
     */
    public function getApiClientSecret()
    {
        return (string) $this->getConfig(static::CONFIG_PATH_GENERAL . 'api_client_secret');
    }

    /**
     * Returns available countries model
     *
     * @return AvailableCountries
     */
    public function getAvailableCountries()
    {
        return $this->_availableCountries;
    }

    /**
     * @return GeoIP
     */
    public function getGeoIP()
    {
        if (!isset($this->_geoIP)) {
            $this->_geoIP = $this->_geoIPFactory->create();
            if ($this->_geoIP->isEnabled()) {
                $this->_geoIP->loadByIP();
            }
        }

        return $this->_geoIP;
    }

    /**
     * Returns order confirmation path
     *
     * @return string
     */
    public function getOrderConfirmationPath()
    {
        return (string) $this->getConfig(static::CONFIG_PATH_GENERAL . 'order_confirmation_path');
    }

    /**
     * Returns order confirmation url
     *
     * @return string
     */
    public function getOrderConfirmationUrl()
    {
        if (!isset($this->_orderConfirmationUrl)) {
            $path = trim($this->getOrderConfirmationPath(), '/');
            $url = trim($this->getStoreManager()->getStore()->getBaseUrl(), '/');
            $this->_orderConfirmationUrl = (!empty($path) ? $url . '/' . $path . '/' : '');
        }

        return $this->_orderConfirmationUrl;
    }

    /**
     * Returns country by ip
     *
     * @return string
     */
    public function getCountryByIp()
    {
        return $this->getGeoIP()->getCountryCode();
    }

    /**
     * Returns currency for specific country
     *
     * @param string $country
     * @param null|string $defaultCurrency
     * @return string
     */
    public function getCurrencyByCountry($country, $defaultCurrency = null)
    {
        if (empty($defaultCurrency)) {
            $defaultCurrency = $this->getDefaultCurrency();
        }

        return $this->getAvailableCountries()->getCurrency((string) $country) ?: $defaultCurrency;
    }

    /**
     * Returns currency by ip
     *
     * @return string
     */
    public function getCurrencyByIp()
    {
        return $this->getGeoIP()->getCountryCurrency();
    }

    /**
     * Returns default country
     *
     * @return string
     */
    public function getDefaultCountry()
    {
        return (string) $this->getConfig('general/country/default');
    }

    /**
     * Returns default currency
     *
     * @return string
     */
    public function getDefaultCurrency()
    {
        return (string) $this->getConfig('currency/options/base');
    }

    /**
     * Returns partner key value
     *
     * @return string
     */
    public function getPartnerKey()
    {
        return (string) $this->getConfig(static::CONFIG_PATH_GENERAL . 'partner_key');
    }

    /**
     * Returns selected country code
     *
     * @return string
     */
    public function getSelectedCountry()
    {
        if (!isset($this->_selectedCountry)) {
            $this->_selectedCountry= $this->_storeManager->getStore()->getCurrentCountryCode();

            if (empty($this->_selectedCountry) || !$this->getAvailableCountries()->isAvailable($this->_selectedCountry)) {
                $this->_selectedCountry = $this->getCountryByIp();

                if (empty($this->_selectedCountry) || !$this->getAvailableCountries()->isAvailable($this->_selectedCountry)) {
                    $this->_selectedCountry = $this->getDefaultCountry();
                }

                $this->saveSelectedCurrency(
                    $this->getCurrencyByCountry(
                        $this->_selectedCountry,
                        $this->getCurrencyByIp()
                    )
                );
            }
        }

        return $this->_selectedCountry;
    }

    /**
     * Returns selected currency code
     *
     * @return string
     */
    public function getSelectedCurrency()
    {
        if (empty($this->_selectedCurrency)) {
            $this->_selectedCurrency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        }

        return $this->_selectedCurrency;
    }

    /**
     * Returns session
     *
     * @return SessionManagerInterface
     */
    public function getSession()
    {
        if (!$this->_session->isSessionExists()) {
            $name = 'store_' . $this->_storeManager->getStore()->getCode();
            $this->_session->setName($name);
            $this->_session->start();
        }

        return $this->_session;
    }

    /**
     * Returns store manager model
     *
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    /**
     * Returns product identifier
     *
     * @return string
     */
    public function getProductIdentifier()
    {
        return $this->getConfig('fedex_crossborder/import_export/identifier', 'entity_id');
    }

    /**
     * Resets country and currency to default
     *
     * @return $this
     */
    public function saveDefaultCountry()
    {
        $this->saveSelectedCountry(
            $this->getDefaultCountry()
        );

        return $this;
    }

    /**
     * Saving selected country code
     *
     * @param string|null $value
     * @param bool $setDefaultCurrency
     * @return $this
     */
    public function saveSelectedCountry($value = null, $setDefaultCurrency = true)
    {
        $this->_selectedCountry = (string) $value;
        $this->_storeManager->getStore()->setCurrentCountryCode($this->_selectedCountry);

        if ($setDefaultCurrency) {
            $this->saveSelectedCurrency(
                $this->getCurrencyByCountry($this->_selectedCountry)
            );
        }

        return $this;
    }

    /**
     * Saving selected currency code
     *
     * @param string $value
     * @return $this
     */
    public function saveSelectedCurrency($value = null)
    {
        $this->_selectedCurrency = (string) $value;
        $this->_storeManager->getStore()->setCurrentCurrencyCode($this->_selectedCurrency);

        return $this;
    }
}