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
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Store\Model\ScopeInterface;

abstract class AbstractImport
{
    const CONFIG_PATH               = 'fedex_crossborder/import_export/';
    const CONFIG_PATH_GENERAL       = 'fedex_crossborder/general/';

    const API_KEY_PATH              = 'key';
    const ENDPOINT_PATH             = '';
    const URL_PATH                  = 'url';
    const TIMEOUT                   = 100;

    const METHOD_GET                = 'GET';
    const METHOD_POST               = 'POST';

    const ERROR                     = 'ERROR [%s]: %s';
    const ERROR_URL_EMPTY           = 'ERROR: Url not defined';
    const ERROR_INCORRECT_RESPONE   = 'Incorrect response format';
    const ERROR_EMPTY_RESPONSE      = 'Unable to read response, or response is empty';
    const ERROR_UNKNOWN             = 'Unknown error';
    const MSG_SEND                  = 'Sent request %s';
    const MSG_RESPONSE              = 'Response: %s';

    const LOG_FILE                  = 'FedEx/CrossBorder/Import.log';

    /**
     * @var mixed
     */
    protected $_data;

    /**
     * @var string
     */
    protected $_errorMessage;

    /**
     * @var bool
     */
    protected $_hasError    = false;

    /**
     * @var array
     */
    protected $_headers = [];

    /**
     * @var ZendClientFactory
     */
    protected $_httpClientFactory;

    /**
     * @var ScopeConfig
     */
    protected $_scopeConfig;

    /**
     * @var string
     */
    protected $_url;

    /**
     * AbstractImport constructor.
     *
     * @param ScopeConfig $scopeConfig
     * @param ZendClientFactory $httpClientFactory
     */
    public function __construct(
        ScopeConfig $scopeConfig,
        ZendClientFactory $httpClientFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_httpClientFactory = $httpClientFactory;
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
     * Adds data
     *
     * @param mixed $data
     * @return $this
     */
    public function addData($data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * Adds header
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addHeader($name, $value)
    {
        $this->_headers[$name] = $value;

        return $this;
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
     * Returns api key
     *
     * @return string
     */
    public function getApiKey()
    {
        return (string) $this->getConfig(self::CONFIG_PATH . self::API_KEY_PATH);
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
     * Returns endpoint value
     *
     * @return string
     */
    public function getEndpoint()
    {
        $value = $this->getConfig(self::CONFIG_PATH . static::ENDPOINT_PATH);
        return (!empty($value) ? '/' . $value : '');
    }

    /**
     * Returns last error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    /**
     * Returns headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Returns timeout
     *
     * @return int
     */
    public function getTimeout()
    {
        return self::TIMEOUT;
    }

    /**
     * Returns api url
     *
     * @return string
     */
    public function getUrl()
    {
        if (!isset($this->_url)) {
            $this->_url = trim(
                $this->getConfig(self::CONFIG_PATH . self::URL_PATH),
                '/'
            ) . $this->getEndpoint() . $this->getUrlParams();
        }

        return $this->_url;
    }

    /**
     * Returns api url parameters
     *
     * @return string
     */
    public function getUrlParams()
    {
        return '';
    }

    /**
     * Checks if last request has error
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->_hasError;
    }

    /**
     * Checks if headers exist
     *
     * @return bool
     */
    public function hasHeaders()
    {
        return (bool) count($this->_headers);
    }

    /**
     * Checks if authorization required
     *
     * @return bool
     */
    public function isAuthRequired() {
        return true;
    }

    /**
     * Send request and returns response data
     *
     * @param string $method
     * @return array
     */
    public function getResponse($method = self::METHOD_GET)
    {
        $this->reset();
        $response = [];
        $url = $this->getUrl();
        if (!empty($url)) {
            set_time_limit(0);
            try {
                if ($this->isAuthRequired()) {
                    $this->addHeader(
                        'Authorization',
                        'Bearer ' . $this->getApiKey()
                    );
                }
                /** @var ZendClient $httpClient */
                $httpClient = $this->_httpClientFactory->create();
                if (!empty($this->_data)) {
                    $httpClient->setRawData($this->_data);
                }
                $httpClient->setUri(
                    $url
                )->setConfig([
                    'timeout' => $this->getTimeout(),
                ])->setHeaders(
                    $this->getHeaders()
                );

                $this->addLog(sprintf(
                    self::MSG_SEND,
                    $url . (!empty($this->_data) ? "\n" . $this->_data : '')
                ));

                $jsonResponse = $httpClient->request($method)->getBody();

                $this->addLog(sprintf(
                    self::MSG_RESPONSE,
                    $jsonResponse
                ));

                $response = $this->validateResponse(json_decode($jsonResponse, true) ?: []);
            } catch (\Exception $exception) {
                $this->_hasError = true;
                $this->_errorMessage = $exception->getMessage();
                $this->addLog(sprintf(
                    self::ERROR,
                    $exception->getCode(),
                    $exception->getMessage()
                ));
            } finally {
                ini_restore('max_execution_time');
            }
        } else {
            $this->_hasError = true;
            $this->_errorMessage = static::ERROR_URL_EMPTY;
            $this->addLog(static::ERROR_URL_EMPTY);
        }

        return $response;
    }

    /**
     * Reset data
     *
     * @return $this
     */
    public function reset()
    {
        $this->_hasError = false;
        $this->_errorMessage = '';

        return $this;
    }

    /**
     * Sets headers
     *
     * @param array $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;

        return $this;
    }

    /**
     * Unset headers
     *
     * @param null|string $name
     * @return $this
     */
    public function unsetHeaders($name = null)
    {
        if (isset($name) && isset($this->_headers[$name])) {
            unset($this->_headers[$name]);
        } else {
            $this->_headers = [];
        }

        return $this;
    }

    /**
     * Validation of response data
     *
     * @param mixed $response
     * @return array
     * @throws \Exception
     */
    public function validateResponse($response) {
        if (empty($response)) {
            throw new \Exception(static::ERROR_EMPTY_RESPONSE);
        }

        if (!is_array($response)) {
            throw new \Exception(static::ERROR_INCORRECT_RESPONE);
        }

        if (isset($response['error'])) {
            if (is_string($response['error'])) {
                throw new \Exception($response['error']);
            } elseif (is_array($response['error']) &&
                (!empty($response['error']['title']) || !empty($response['error']['message']))
            ) {
                $message = !empty($response['error']['title']) ? $response['error']['title'] . ': ' : '';
                $message.= !empty($response['error']['message']) ? $response['error']['message'] : '';

                throw new \Exception($message);
            } else {
                throw new \Exception(static::ERROR_UNKNOWN);
            }
        }

        if (isset($response['success']) && !$response['success']) {
            if (!empty($response['title']) || !empty($response['message'])) {
                $message = !empty($response['title']) ? $response['title'] . ': ' : '';
                $message.= !empty($response['message']) ? $response['message'] : '';

                throw new \Exception($message);
            } else {
                throw new \Exception(static::ERROR_UNKNOWN);
            }
        }

        return $response;
    }
}