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
namespace FedEx\CrossBorder\Model\Carrier;

use FedEx\Core\Model\Log;
use FedEx\CrossBorder\Model\Checkout\DomesticCost;
use FedEx\CrossBorder\Model\Checkout\LandedCost;
use FedEx\CrossBorder\Model\MerchantControl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Rate\Result;
use Psr\Log\LoggerInterface;

class Shipping extends AbstractCarrier implements CarrierInterface
{
    const CODE                  = 'fdxcb';
    const ERROR                 = 'Something went wrong. Couldn\'t be calculated shipping cost.';
    const LOG_FILE              = 'FedEx/CrossBorder/ShippingMethod.log';
    const METHOD_EXPRESS        = 'express';
    const METHOD_STANDARD       = 'standard';
    const METHOD_ECONOMY        = 'economy';
    const MSG_COLLECTED         = "Collected successful: \n%s";
    const MSG_COLLECTING        = 'Collecting rates process started';
    const MSG_METHOD_DISABLED   = 'The method "%s" disabled';
    const MSG_PREPARED          = 'Found %d(s) methods:%s';
    const MSG_PREPARING         = 'Preparing allowed methods';

    /**
     * @var string
     */
    protected $_code        = self::CODE;

    /**
     * @var DomesticCost
     */
    protected $_domesticCost;

    /**
     * @var bool
     */
    protected $_isEmpty     = true;

    /**
     * @var LandedCost
     */
    protected $_landedCost;

    /**
     * @var MerchantControl
     */
    protected $_merchantControl;

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var array
     */
    protected $_serviceList = [
        0   => self::METHOD_EXPRESS,
        1   => self::METHOD_STANDARD,
        2   => self::METHOD_ECONOMY,
    ];

    /**
     * Shipping constructor.
     *
     * @param DomesticCost $domesticCost
     * @param LandedCost $landedCost
     * @param ResultFactory $rateResultFactory
     * @param MerchantControl $merchantControl
     * @param MethodFactory $rateMethodFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        DomesticCost $domesticCost,
        LandedCost $landedCost,
        ResultFactory $rateResultFactory,
        MerchantControl $merchantControl,
        MethodFactory $rateMethodFactory,
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->_domesticCost = $domesticCost;
        $this->_landedCost = $landedCost;
        $this->_merchantControl = $merchantControl;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Returns shipping address information
     *
     * @param RateRequest $request
     * @return array
     */
    protected function _convertToShippingAddressData(RateRequest $request)
    {
        $isValid = $request->getDestCountryId() == $this->_landedCost->getHelper()->getSelectedCountry();
        $street = explode("\n", $request->getDestStreet());

        return [
            'shipmentDestinationAddress1'           => ($isValid && isset($street[0]) ? $street[0] : ''),
            'shipmentDestinationAddress2'           => ($isValid && isset($street[1]) ? $street[1] : ''),
            'shipmentDestinationCity'               => ($isValid ? $request->getDestCity() : ''),
            'shipmentDestinationStateOrProvince'    => ($isValid ? $request->getDestRegionCode() : ''),
            'shipmentDestinationZip'                => ($isValid ? $request->getDestPostcode() : ''),
            'shipmentDestinationCountry'            => $this->_landedCost->getHelper()->getSelectedCountry(),
        ];
    }

    /**
     * Adds log
     *
     * @param string $message
     * @return $this
     */
    public function addLog($message)
    {
        if ($this->_landedCost->getHelper()->isLogsEnabled()) {
            Log::Info($message, static::LOG_FILE);
        }

        return $this;
    }

    /**
     * Adds error log
     *
     * @param string $message
     * @return $this
     */
    public function addError($message)
    {
        if ($this->_landedCost->getHelper()->isLogsEnabled()) {
            Log::Error($message, static::LOG_FILE);
        }

        return $this;
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     * @return DataObject|bool|null
     * @api
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->isActive()) {
            $this->addError(sprintf(
                static::MSG_METHOD_DISABLED,
                $this->_code
            ));

            return false;
        }

        $this->addLog(static::MSG_COLLECTING);
        try {
            /** @var Result $result */
            $result = $this->_rateResultFactory->create();
            $this->_landedCost->setDomesticShippingCost(
                $this->getDomesticShippingCost()
            )->setItems(
                $request->getAllItems()
            )->setShipmentDestinationData(
                $this->_convertToShippingAddressData($request)
            )->setShipmentOriginCountry(
                $request->getOrigCountryId()
            );

            foreach ($this->getServiceList() as $service => $methodCode) {
                if ($method = $this->getMethod($service)) {
                    $result->append(
                        $method
                    );
                }
            }

            if ($this->isEmpty()) {
                $this->addError($this->getError());
            } else {
                $this->addLog(sprintf(
                    static::MSG_COLLECTED,
                    json_encode($result->asArray(), JSON_PRETTY_PRINT)
                ));
            }
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        return (!$this->isEmpty() ? $result : $this->getError());
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     * @api
     */
    public function getAllowedMethods()
    {
        $this->addLog(static::MSG_PREPARING);
        $methods = [];
        foreach ($this->_serviceList as $service) {
            $methods[$service] = $this->getMethodName($service);
        }
        $this->addLog(sprintf(
            static::MSG_PREPARED,
            count($methods),
            !empty($methods) ? "\n" . json_encode($methods, JSON_PRETTY_PRINT) : ''
        ));

        return $methods;
    }

    /**
     * Returns domestic shipping cost
     *
     * @return float
     */
    public function getDomesticShippingCost()
    {
        return (!$this->isMerchantEnabled() ? $this->_domesticCost->getDomesticShippingCost() : 0);
    }

    /**
     * Returns error result
     *
     * @return Error
     */
    public function getError()
    {
        /** @var Error $error */
        $error = $this->_rateErrorFactory->create();
        $error->setCarrier(
            $this->_code
        )->setCarrierTitle(
            $this->getTitle()
        )->setErrorMessage(
            $this->getErrorMessage()
        );

        return $error;
    }

    /**
     * Returns error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        $error = (string) $this->getConfigData('specificerrmsg');

        return (!empty($error) ? $error : static::ERROR);
    }

    /**
     * Returns method
     *
     * @param $service
     * @return Method|bool
     */
    public function getMethod($service)
    {
        $methodCode = $this->getMethodCode($service);
        if (empty($methodCode)) {
            return false;
        }

        $response = $this->_landedCost->setService(
            $service
        )->setShippingMethod(
            $this->getCarrierCode() . '_' . $methodCode
        )->reCalculate();

        if (!$response || $response->error) {
            return false;
        }

        /** @var Method $method */
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier(
            $this->getCarrierCode()
        )->setCarrierTitle(
            $this->getTitle()
        )->setMethod(
            $methodCode
        )->setMethodTitle(
            $this->getMethodName($methodCode)
        )->setPrice(
            $response->shippingCost + $response->lossAndDamageProtectionCost
        )->setCost(
            $response->shippingCost + $response->lossAndDamageProtectionCost
        );

        $this->_isEmpty = false;

        return $method;
    }

    /**
     * Returns method code
     *
     * @param int $service
     * @return mixed|string
     */
    public function getMethodCode($service)
    {
        return ($this->_serviceList[$service] ?? '');
    }

    /**
     * Returns method name
     *
     * @param string $methodName
     * @return string
     */
    public function getMethodName($methodName)
    {
        return (string) $this->getConfigData($methodName . '_name');
    }

    /**
     * @return array
     */
    public function getServiceList()
    {
        return $this->_serviceList;
    }

    /**
     * Returns carrier title
     *
     * @return string
     */
    public function getTitle()
    {
        return (string) $this->getConfigData('title');
    }

    /**
     * Determine whether current carrier enabled for activity
     *
     * @return bool
     */
    public function isActive()
    {
        return parent::isActive() && $this->_landedCost->getHelper()->isInternational();
    }


    /**
     * Checks if list of available methods is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return (bool) $this->_isEmpty;
    }

    /**
     * Checks if merchant control is enabled
     *
     * @return bool
     */
    public function isMerchantEnabled()
    {
        return $this->_merchantControl->isEnabled();
    }
}
