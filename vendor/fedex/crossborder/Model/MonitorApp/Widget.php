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
namespace FedEx\CrossBorder\Model\MonitorApp;

use FedEx\Core\Model\Log;
use FedEx\CrossBorder\Helper\Data as Helper;
use FedEx\CrossBorder\Model\MerchantControl;
use FedExCrossBorder\Adapter\GuzzleHttpAdapter;
use FedExCrossBorder\Auth\Credentials;
use FedExCrossBorder\Monitoring\MonitoringClient;
use FedExCrossBorder\Tracking\Entity\CustomerInfo;
use FedExCrossBorder\Tracking\Entity\MerchantCredential;
use FedExCrossBorder\Tracking\Entity\TrackingParam;

class Widget
{
    const API_URL_PATH      = 'fedex_crossborder/api/';
    const LOG_FILE          = 'FedEx/CrossBorder/MonitorApp.log';
    const ERROR_LOG         = 'Error [%s]: %s';

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var MerchantControl
     */
    protected $_merchantControl;

    /**
     * Widget constructor.
     *
     * @param Helper $helper
     * @param MerchantControl $merchantControl
     */
    public function __construct(
        Helper $helper,
        MerchantControl $merchantControl
    ) {
        $this->_helper = $helper;
        $this->_merchantControl = $merchantControl;
    }

    /**
     * Adds log
     *
     * @param string $message
     * @return $this
     */
    public function addLog($message)
    {
        if ($this->getHelper()->isLogsEnabled()) {
            Log::Info($message, static::LOG_FILE);
        }

        return $this;
    }

    /**
     * Checks if Monitor App can be shown
     *
     * @return bool
     */
    public function canShow()
    {
        return $this->_merchantControl->isEnabled() && $this->_merchantControl->canShowMonitorApp();
    }

    /**
     * @return Helper
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Returns API url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getHelper()->getConfig(static::API_URL_PATH . 'monitoring_url');
    }


    /**
     * Returns widget code
     *
     * @param bool $isAutoOpen
     * @return string
     */
    public function toHtml($isAutoOpen = false)
    {
        if ($this->_merchantControl->isEnabled()) {
            try {
                $credentials = new Credentials(
                    $this->getHelper()->getApiClientId(),
                    $this->getHelper()->getApiClientSecret(),
                    $this->getHelper()->getPartnerKey()
                );
                $guzzleAdapter = new GuzzleHttpAdapter();
                $monitoringClient = new MonitoringClient(
                    $credentials,
                    $guzzleAdapter,
                    $this->getUrl()
                );

                $trackingParam = new TrackingParam();
                $merchantCredential = new MerchantCredential();
                $merchantCredential->setPartnerKey(
                    $this->getHelper()->getPartnerKey()
                );
                $customerInfo = new CustomerInfo();
                $trackingParam->setMerchantCredential($merchantCredential);
                $trackingParam->setCustomerInfo($customerInfo);
                $trackingParam->setAutoOpen($isAutoOpen);

                return $monitoringClient->getWidget($trackingParam);
            } catch (\FedExCrossBorder\Exception\HttpException $exception) {
                $this->addLog(sprintf(
                    static::ERROR_LOG,
                    $exception->getCode(),
                    $exception->getMessage()
                ));
            }
        }

        return '';
    }
}