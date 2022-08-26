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
namespace FedEx\CrossBorder\Model\PackNotification;

use FedEx\CrossBorder\Model\AbstractImport;
use FedEx\CrossBorder\Helper\Data as Helper;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;

class Sender extends AbstractImport
{
    const ENDPOINT_PATH = 'pack_notification_path';
    const LOG_FILE      = 'FedEx/CrossBorder/PackNotification.log';

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * Sender constructor.
     *
     * @param Helper $helper
     * @param ScopeConfig $scopeConfig
     * @param ZendClientFactory $httpClientFactory
     */
    public function __construct(
        Helper $helper,
        ScopeConfig $scopeConfig,
        ZendClientFactory $httpClientFactory)
    {
        $this->_helper = $helper;
        parent::__construct($scopeConfig, $httpClientFactory);
    }

    /**
     * Returns API client id value
     *
     * @return string
     */
    public function getApiClientId()
    {
        return $this->_helper->getApiClientId();
    }

    /**
     * Returns API client secret value
     *
     * @return string
     */
    public function getApiClientSecret()
    {
        return $this->_helper->getApiClientSecret();
    }

    /**
     * Returns partner key value
     *
     * @return string
     */
    public function getPartnerKey()
    {
        return $this->_helper->getPartnerKey();
    }

    /**
     * Returns document data
     *
     * @param string $url
     * @return array|string
     */
    public function getDocument($url)
    {
        $this->reset();
        $response = '';

        if (!empty($url)) {
            set_time_limit(0);
            try {
                $this->addHeader(
                    'Authorization',
                    'Basic ' . base64_encode($this->getApiClientId() . ':' . $this->getApiClientSecret())
                )->addHeader(
                    'X-FCB-Partner-Key',
                    $this->getPartnerKey()
                );
                /** @var ZendClient $httpClient */
                $httpClient = $this->_httpClientFactory->create();
                $httpClient->setUri(
                    $url
                )->setConfig([
                    'timeout' => $this->getTimeout(),
                ])->setHeaders(
                    $this->getHeaders()
                );

                $this->addLog(sprintf(
                    self::MSG_SEND,
                    $url
                ));

                $response = $httpClient->request('GET')->getBody();
                if ($jsonResponse = json_decode($response, true)) {
                    $this->addLog(sprintf(
                        self::MSG_RESPONSE,
                        $response
                    ));

                    $response = $this->validateResponse($jsonResponse ?: []);
                }
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
}