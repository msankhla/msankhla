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
namespace FedEx\CrossBorder\Model\Currency\Import;

use FedEx\Core\Model\Log;
use Magento\Directory\Model\Currency\Import\AbstractImport;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Store\Model\ScopeInterface;

class Rates extends AbstractImport
{
    const ERROR                     = 'ERROR [%s]: %s';
    const ERROR_NO_RATE             = 'We can\'t retrieve a rate from %1 for %2.';
    const ERROR_VALIDATION          = 'Currency rates can\'t be retrieved.';
    const LOG_FILE                  = 'FedEx/CrossBorder/ImportCurrency.log';
    const MSG_SEND                  = 'Sent request %s';
    const MSG_RESPONSE              = 'Response: %s';

    /**
     * @var ZendClientFactory
     */
    protected $_httpClientFactory;

    /**
     * @var string
     */
    protected $_url;

    /**
     * @var ScopeConfig
     */
    protected $_scopeConfig;

    /**
     * @var string
     */
    protected $_serviceHost;

    /**
     * FedEx constructor.
     *
     * @param CurrencyFactory $currencyFactory
     * @param ScopeConfig $scopeConfig
     * @param ZendClientFactory $httpClientFactory
     */
    public function __construct(
        CurrencyFactory $currencyFactory,
        ScopeConfig $scopeConfig,
        ZendClientFactory $httpClientFactory
    ) {
        $this->_httpClientFactory = $httpClientFactory;
        $this->_scopeConfig = $scopeConfig;

        parent::__construct($currencyFactory);
    }

    /**
     * Retrieve rate
     *
     * @param   string $currencyFrom
     * @param   string $currencyTo
     * @return  float
     */
    protected function _convert($currencyFrom, $currencyTo)
    {
        return 1;
    }

    /**
     * Return currencies convert rates in batch mode
     *
     * @param array $data
     * @param string $currencyFrom
     * @param array $currenciesTo
     * @return array
     */
    protected function _convertBatch($data, $currencyFrom, $currenciesTo)
    {
        $rates = $this->getRates($currencyFrom);

        foreach ($currenciesTo as $to) {
            if ($currencyFrom === $to) {
                $data[$currencyFrom][$to] = $this->_numberFormat(1);
            } else {
                if (isset($rates[$to])) {
                    $data[$currencyFrom][$to] = $this->_numberFormat($rates[$to]);
                } else {
                    $this->_messages[] = __(
                        self::ERROR_NO_RATE,
                        $this->getServiceHost(),
                        $to
                    );
                    $data[$currencyFrom][$to] = null;
                }
            }
        }

        return $data;
    }

    /**
     * Make empty rates for provided currencies.
     *
     * @param array $currenciesTo
     * @return array
     */
    protected function _makeEmptyResponse($currenciesTo)
    {
        return array_fill_keys($currenciesTo, null);
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
     * Checks if logs enabled
     *
     * @return bool
     */
    public function isLogsEnabled()
    {
        return (bool) $this->_scopeConfig->getValue(
            'fedex_crossborder/general/logs',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns api key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->_scopeConfig->getValue(
            'fedex_crossborder/import_export/key',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns rates for specific currency
     *
     * @param string $currencyFrom
     * @return array
     */
    public function getRates($currencyFrom)
    {
        $rates = [];

        $response = $this->getServiceResponse($currencyFrom);
        if ($this->validateResponse($response)) {
            foreach ($response as $item) {
                if (!empty($item['currency']) && !empty($item['rate'])) {
                    $rates[$item['currency']] = (double) $item['rate'];
                }
            }
        }

        return $rates;
    }

    /**
     * Returns service host
     *
     * @return string
     */
    public function getServiceHost()
    {
        if (!isset($this->_serviceHost)) {
            $list = parse_url($this->getServiceUrl());
            $this->_serviceHost = (isset($list['scheme']) ? $list['scheme'] . '://' : '') . $list['host'];
        }

        return $this->_serviceHost;
    }

    /**
     * Returns service response data
     *
     * @param string $currencyFrom
     * @return array
     */
    public function getServiceResponse($currencyFrom)
    {
        $response = [];
        $url = $this->getServiceUrl();
        if (!empty($url)) {
            set_time_limit(0);
            try {
                $this->addLog(sprintf(
                    self::MSG_SEND,
                    $url . '/' . (!empty($currencyFrom) ? '?baseCurrency=' . $currencyFrom : '')
                ));
                /** @var ZendClient $httpClient */
                $httpClient = $this->_httpClientFactory->create();
                $jsonResponse = $httpClient->setUri(
                    $url . '/' . (!empty($currencyFrom) ? '?baseCurrency=' . $currencyFrom : '')
                )->setConfig(
                    [
                        'timeout' => $this->_scopeConfig->getValue(
                            'currency/fedex_crossborder/timeout',
                            ScopeInterface::SCOPE_STORE
                        ),
                    ]
                )->setHeaders(
                    'Authorization',
                    'Bearer ' . $this->getApiKey()
                )->request(
                    'GET'
                )->getBody();

                $this->addLog(sprintf(
                    self::MSG_RESPONSE,
                    $jsonResponse
                ));

                $response = json_decode($jsonResponse, true) ?: [];
            } catch (\Exception $exception) {
                $this->addLog(sprintf(
                    self::ERROR,
                    $exception->getCode(),
                    $exception->getMessage()
                ));
            } finally {
                ini_restore('max_execution_time');
            }
        }

        return $response;
    }

    /**
     * Returns service url
     *
     * @return string
     */
    public function getServiceUrl()
    {
        if (!isset($this->_url)) {
            $this->_url = trim(
                $this->_scopeConfig->getValue(
                    'fedex_crossborder/import_export/url',
                    ScopeInterface::SCOPE_STORE
                ),
                '/'
            );

            if (!empty($this->_url)) {
                $path = trim(
                    $this->_scopeConfig->getValue(
                        'fedex_crossborder/import_export/currency_rates_path',
                        ScopeInterface::SCOPE_STORE
                    ),
                    '/'
                );

                $this->_url .= '/' . $path;
            }
        }

        return $this->_url;
    }

    /**
     * @return array
     */
    public function fetchRates()
    {
        $data = [];
        $currencies = $this->_getCurrencyCodes();
        $defaultCurrencies = $this->_getDefaultCurrencyCodes();

        foreach ($defaultCurrencies as $currencyFrom) {
            if (!isset($data[$currencyFrom])) {
                $data[$currencyFrom] = [];
            }
            $data = $this->_convertBatch($data, $currencyFrom, $currencies);
            ksort($data[$currencyFrom]);
        }

        return $data;
    }

    /**
     * Validate rates response.
     *
     * @param array $response
     * @return bool
     */
    public function validateResponse($response)
    {
        if (!is_array($response) || isset($response['error'])) {
            $this->_messages[] = isset($response['error']) ? $response['error'] : __(self::ERROR_VALIDATION);
            return false;
        }

        return true;
    }
}
