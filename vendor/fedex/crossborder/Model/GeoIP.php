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

use FedEx\Core\Model\Log;
use FedEx\CrossBorder\Api\Data\GeoIPInterface;
use FedEx\CrossBorder\Model\ResourceModel\GeoIP as ResourceModel;
use FedExCrossBorder\Adapter\GuzzleHttpAdapter;
use FedExCrossBorder\Auth\Credentials;
use FedExCrossBorder\Geolocation\Entity\Geolocation;
use FedExCrossBorder\Geolocation\GeolocationClient;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

class GeoIP extends AbstractModel implements
    IdentityInterface,
    GeoIPInterface
{
    const CACHE_TAG                     = 'fedex_crossborder_geoip';

    const CONFIG_PATH_API               = 'fedex_crossborder/api/';
    const CONFIG_PATH_GENERAL           = 'fedex_crossborder/general/';
    const CONFIG_PATH_GEO_IP            = 'fedex_crossborder/geo_ip/';

    const LOG_FILE                      = 'FedEx/CrossBorder/GeoIP.log';
    const ERROR_LOG                     = '[%s] Error [%s]: %s';
    const MSG_RECEIVED                  = '[%s] Received data: %s';
    const TIMEOUT                       = 100;


    /**
     * @var string
     */
    protected $_cacheTag                = 'fedex_crossborder_geoip';

    /**
     * @var string
     */
    protected $_eventPrefix             = 'fedex_crossborder_geoip';

    /**
     * @var array
     */
    protected $_ipFields                = [
        'HTTP_CLIENT_IP',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR',
    ];

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * GeoIP constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Adds log
     *
     * @param mixed $message
     * @return $this
     */
    public function addLog($message)
    {
        if ($this->isLogsEnabled()) {
            Log::Info($message, static::LOG_FILE);
        }

        return $this;
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
     * Returns country code
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->getData(self::COUNTRY_CODE);
    }

    /**
     * Returns country default currency
     *
     * @return string
     */
    public function getCountryCurrency()
    {
        return $this->getData(self::COUNTRY_CURRENCY);
    }

    /**
     * Returns IP
     *
     * @return string
     */
    public function getIp()
    {
        if (!$this->hasData('ip')) {
            foreach ($this->_ipFields as $field) {
                if (!empty($_SERVER[$field])) {
                    if ($field == 'HTTP_X_FORWARDED_FOR') {
                        $ips = explode(',', $_SERVER[$field]);
                        $ip = trim($ips[0]);
                    } else {
                        $ip = $_SERVER[$field];
                    }

                    $this->setIp($ip);
                    break;
                }
            }
        }

        return $this->getData('ip');
    }

    /**
     * Returns geo data by ip
     *
     * @param null $ip
     * @return bool|Geolocation
     */
    public function getIpData($ip = null)
    {
        if (!isset($ip)) {
            $ip = $this->getIp();
        }

        if (!empty($ip)) {
            $url = trim($this->getServiceUrl(), '/');
            if (!empty($url) && !empty($ip)) {
                set_time_limit(0);
                try {
                    $credentials = new Credentials(
                        $this->getApiClientId(),
                        $this->getApiClientSecret(),
                        $this->getPartnerKey()
                    );
                    $client = new GeolocationClient(
                        $credentials,
                        new GuzzleHttpAdapter(),
                        $url
                    );

                    $data = $client->getCountry($ip);
                } catch (\Exception $exception) {
                    $this->addLog(sprintf(
                        static::ERROR_LOG,
                        $ip,
                        $exception->getCode(),
                        $exception->getMessage()
                    ));
                } finally {
                    ini_restore('max_execution_time');
                }
            }
        }

        return isset($data) ? $data : false;
    }

    /**
     * Returns config value
     *
     * @param string $path
     * @param string $scopeType
     * @return mixed
     */
    public function getConfig($path, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->_scopeConfig->getValue($path, $scopeType);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
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
     * Returns api url
     *
     * @return string
     */
    public function getServiceUrl()
    {
        return $this->getConfig(static::CONFIG_PATH_API . 'geo_ip_url');
    }

    /**
     * Checks if enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->getConfig(static::CONFIG_PATH_GENERAL . 'enable') &&
            $this->getConfig(static::CONFIG_PATH_GEO_IP . 'enable');
    }

    /**
     * Checks if logs enabled
     *
     * @return bool
     */
    public function isLogsEnabled()
    {
        return (bool) $this->getConfig(static::CONFIG_PATH_GENERAL . 'logs');
    }

    /**
     * @param null|string $ip
     * @return $this
     */
    public function loadByIP($ip = null)
    {
        if (!isset($ip)) {
            $ip = $this->getIp();
        }

        $this->load($ip, 'ip');
        if (!$this->getId()) {
            $geolocation = $this->getIpData($ip);
            if ($geolocation) {
                $this->setData([
                    'ip'                => $ip,
                    'country_code'      => $geolocation->getCountryCode(),
                    'country_currency'  => $geolocation->getCountryCurrency()
                ])->save();
            }
        }

        return $this;
    }

    /**
     * Sets ip
     *
     * @param string $ip
     * @return $this
     */
    public function setIp($ip)
    {
        return $this->setData(self::IP, $ip);
    }

    /**
     * Sets country code
     *
     * @param string $code
     * @return $this
     */
    public function setCountryCode($code)
    {
        return $this->setData(self::COUNTRY_CODE, $code);
    }

    /**
     * Sets country default currency
     *
     * @param string $code
     * @return $this
     */
    public function setCountryCurrency($code)
    {
        return $this->setData(self::COUNTRY_CURRENCY, $code);
    }
}