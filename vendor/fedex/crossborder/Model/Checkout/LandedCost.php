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
namespace FedEx\CrossBorder\Model\Checkout;

use FedEx\Core\Model\Log;
use FedEx\CrossBorder\Api\TaxManagementInterface;
use FedEx\CrossBorder\Helper\Data as Helper;
use FedEx\CrossBorder\Model\ProductValidator;
use FedExCrossBorder\Connect\ConnectSoapClient;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class LandedCost
{
    const API_URL_PATH          = 'fedex_crossborder/api/';
    const DEFAULT_LANGUAGE      = 'en';
    const METHOD                = 'ConnectLandedCost';
    const LOG_FILE              = 'FedEx/CrossBorder/LandedCost.log';
    const ERROR_LOG             = 'Error [%s]: %s';
    const ERROR_NOT_FOUND       = 'No available products';

    /**
     * @var ProductValidator
     */
    protected $_productValidator;

    /**
     * @var ConnectSoapClient
     */
    protected $_client;

    /**
     * @var array
     */
    protected $_data;

    /**
     * @var float
     */
    protected $_domesticShippingCost    = 0;

    /**
     * @var array
     */
    protected $_fields                  = [
        'merchantKey',
        'sellerIdKey',
        'language',
        'privateIndividuals',
        'items',
        'shipmentOriginCountry',
        'shipmentDestinationData',
        'domesticShippingCost',
        'internationalShippingCost',
        'internationalShippingName',
        'lossAndDamageProtectionFlag',
        'currency',
        'currencyConversionRate',
        'service',
    ];

    /**
     * @var array
     */
    protected $_items;

    /**
     * @var array
     */
    protected $_itemsMap;

    /**
     * @var int
     */
    protected $_quoteId;

    /**
     * @var mixed
     */
    protected $_response;

    /**
     * @var int
     */
    protected $_service                 = 1;

    /**
     * @var array
     */
    protected $_shipmentDestinationData = [
        'shipmentDestinationAddress1'           => '',
        'shipmentDestinationAddress2'           => '',
        'shipmentDestinationCity'               => '',
        'shipmentDestinationStateOrProvince'    => '',
        'shipmentDestinationZip'                => '',
        'shipmentDestinationCountry'            => '',
    ];

    protected $_shipmentOriginCountry   = 'US';

    /**
     * @var string
     */
    protected $_shippingMethod;

    /**
     * @var TaxManagementInterface
     */
    protected $_taxManagement;

    /**
     * LandedCost constructor.
     *
     * @param ProductValidator $productValidator
     * @param TaxManagementInterface $taxManagement
     */
    public function __construct(
        ProductValidator $productValidator,
        TaxManagementInterface $taxManagement
    ) {
        $this->_productValidator = $productValidator;
        $this->_taxManagement = $taxManagement;
    }

    /**
     * Adds error log for product
     *
     * @param Product $product
     * @return $this
     */
    protected function _addErrorProductLog(Product $product)
    {
        $code = $this->getHelper()->getProductIdentifier();
        $data = [
            'id'                    => $product->getData($code),
            'sku'                   => $product->getSku(),
            'name'                  => $product->getName(),
            'fdx_country_of_origin' => $product->getFdxCountryOfOrigin(),
            'fdx_haz_flag'          => (bool) $product->getFdxHazFlag(),
            'fdx_import_flag'       => $product->getFdxImportFlag(),
        ];

        $this->addLog('Incorrect product data: ' . json_encode($data));

        return $this;
    }

    /**
     * Converting quote item element into product data
     *
     * @param QuoteItem $item
     * @return array
     */
    protected function _convertItem(QuoteItem $item)
    {
        $code = $this->getHelper()->getProductIdentifier();
        $data = [
            'productID'         => $item->getProduct()->getData($code),
            'quantity'          => $item->getQty(),
            'country_of_origin' => $item->getProduct()->getFdxCountryOfOrigin(),
        ];

        if ($item->getParentItemId()) {
            $parent = $item->getParentItem();
            $data['quantity'] = $data['quantity'] * $parent->getQty();

            switch ($parent->getProductType()) {
                case 'configurable':
                    $data['price'] = ($parent->getBaseRowTotal() - $parent->getBaseDiscountAmount()) / $data['quantity'];
                    break;
                default:
                    $data['price'] = ($item->getBaseRowTotal() - $item->getBaseDiscountAmount()) / $data['quantity'];
            }
        } else {
            $data['price'] = ($item->getBaseRowTotal() - $item->getBaseDiscountAmount()) / $data['quantity'];
        }

        $this->addLog('Adding product: ' . json_encode($data));

        return $data;
    }

    /**
     * Adds log
     *
     * @param mixed $message
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
     * Returns landed cost calculated data
     *
     * @param bool $isNew
     * @return mixed
     */
    public function calculate($isNew = false)
    {
        if ($this->isItemsExist()) {
            try {
                if (!isset($this->_response) || $isNew) {
                    $methodName = $this->getMethod();
                    $this->_response = $this->getClient($isNew)->$methodName(
                        $this->getData($isNew)
                    );
                    $this->addLog(print_r($this->getResponse(), true));
                    $this->saveTaxes();
                }

                return $this->getResponse();
            } catch (\Exception $exception) {
                $this->addLog(sprintf(
                    static::ERROR_LOG,
                    $exception->getCode(),
                    $exception->getMessage()
                ));
            }
        } else {
            $this->addLog(sprintf(
                static::ERROR_LOG,
                404,
                __(static::ERROR_NOT_FOUND)
            ));
        }

        return false;
    }

    /**
     * Checks if available products exist
     *
     * @return bool
     */
    public function isItemsExist()
    {
        return count($this->getItems()) > 0;
    }

    /**
     * Returns soap client model
     *
     * @param bool $isNew
     * @return ConnectSoapClient
     */
    public function getClient($isNew = false)
    {
        if (!isset($this->_client) || $isNew) {
            $this->_client = new ConnectSoapClient(
                $this->getUrl()
            );
        }

        return $this->_client;
    }

    /**
     * Returns the currency in which will be returned cost
     * 0 - USD
     * 1 - The currency of the shipping destination country and territory
     *
     * @return int
     */
    public function getCurrency()
    {
        return 0;
    }

    /**
     * Returns request data
     *
     * @param bool $isNew
     * @return array
     */
    public function getData($isNew = false)
    {
        if (!isset($this->_data) || $isNew) {
            $this->_data = [];
            foreach ($this->_fields as $field) {
                $method = 'get' . ucfirst($field);
                if ($field == 'shipmentDestinationData') {
                    $this->_data = array_merge($this->_data, $this->$method());
                } elseif (method_exists($this, $method)) {
                    $this->_data[$field] = $this->$method();
                } else {
                    $this->_data[$field] = '';
                }
            }
        }

        $this->addLog([
            'Action'    => 'Preparing Data',
            'Quote ID'  => $this->_quoteId ?? 0,
            'Data'      => $this->_data,
        ]);

        return $this->_data;
    }

    /**
     * Returns domestic shipping cost
     *
     * @return string
     */
    public function getDomesticShippingCost()
    {
        return number_format($this->_domesticShippingCost, 2, '.', '');
    }

    /**
     * Returns helper
     *
     * @return Helper
     */
    public function getHelper()
    {
        return $this->_productValidator->getHelper();
    }

    /**
     * Returns items data
     *
     * @return array
     */
    public function getItems()
    {
        return (isset($this->_items) ? $this->_items : []);
    }

    /**
     * Returns language code
     *
     * @return string
     */
    public function getLanguage()
    {
        return static::DEFAULT_LANGUAGE;
    }

    /**
     * Returns loss and damage protection flag
     *
     * @return bool
     */
    public function getLossAndDamageProtectionFlag()
    {
        return true;
    }

    /**
     * Returns merchant key
     *
     * @return string
     */
    public function getMerchantKey()
    {
        return $this->getHelper()->getPartnerKey();
    }

    /**
     * Returns method name
     *
     * @return string
     */
    public function getMethod()
    {
        return static::METHOD;
    }

    /**
     * Returns private individuals value
     *
     * @return string
     */
    public function getPrivateIndividuals()
    {
        /** ToDo add specific logic if needed */
        return '';
    }

    /**
     * Returns response
     *
     * @return mixed
     */
    public function getResponse()
    {
        return (isset($this->_response->ConnectLandedCostResult) ? $this->_response->ConnectLandedCostResult : null);
    }

    /**
     * Returns service value
     *
     * @return int
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * Returns shipping origin country code
     *
     * @return string
     */
    public function getShipmentOriginCountry()
    {
        return (string) (!empty($this->_shipmentOriginCountry) ? $this->_shipmentOriginCountry : 'US');
    }

    /**
     * Returns shipment destination data
     *
     * @return array
     */
    public function getShipmentDestinationData()
    {
        if (empty($this->_shipmentDestinationData['shipmentDestinationCountry'])) {
            $this->_shipmentDestinationData['shipmentDestinationCountry'] = $this->getHelper()->getSelectedCountry();
        }

        return $this->_shipmentDestinationData;
    }

    /**
     * Returns shipping method
     *
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->_shippingMethod;
    }

    /**
     * Returns tax management
     *
     * @return TaxManagementInterface
     */
    public function getTaxManagement()
    {
        return $this->_taxManagement;
    }

    /**
     * Returns api url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getHelper()->getConfig(static::API_URL_PATH . 'connect_url');
    }

    /**
     * Regenarate data and resend landed cost request
     *
     * @return mixed
     */
    public function reCalculate()
    {
        $this->_response = null;
        $this->_data = null;

        return $this->calculate();
    }

    /**
     * Save items taxes
     *
     * @return $this
     */
    public function saveTaxes()
    {
        $response = $this->getResponse();
        if ($response && !$response->error &&
            isset($response->items->item)
        ) {
            if (!is_array($response->items->item)) {
                $response->items->item = [$response->items->item];
            }

            foreach ($response->items->item as $key => $item) {
                if (isset($this->_itemsMap[$key])) {
                    $this->getTaxManagement()->addItemTax([
                        'quote_id'          => (int) $this->_quoteId,
                        'item_id'           => (int) $this->_itemsMap[$key],
                        'shipping_method'   => $this->getShippingMethod(),
                        'tax_amount'        => (float) $item->taxCost,
                        'duty_amount'       => (float) $item->dutyCost,
                    ]);
                }
            }
        }

        return $this;
    }

    /**
     * Sets domestic shipping cost
     *
     * @param float $value
     * @return $this
     */
    public function setDomesticShippingCost($value)
    {
        $this->_domesticShippingCost = (float) $value;

        return $this;
    }

    /**
     * Sets items
     *
     * @param array $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->addLog('Preparing Products...');
        $this->_items = [];
        $this->_itemsMap = [];
        $this->_quoteId = null;
        foreach ($items as $item) {
            if ($item->getHasChildren()) {
                continue;
            }

            if ($this->_productValidator->isProductAvailable($item->getProduct())) {
                if (!isset($this->_quoteId)) {
                    $this->_quoteId = $item->getQuoteId();
                }
                $this->_itemsMap[] = $item->getId();
                $this->_items[] = $this->_convertItem($item);
            } else {
                $this->_addErrorProductLog($item->getProduct());
            }
        }

        $this->addLog('Found ' . count($this->_items) . ' product(s)');

        return $this;
    }

    /**
     * Sets service
     *
     * @param int $value
     * @return $this
     */
    public function setService($value)
    {
        $value = intval($value);
        $this->_service = ($value < 0 || $value > 2 ? 1 : $value);

        return $this;
    }

    /**
     * Sets shipment destination data
     *
     * @param array $value
     * @return $this
     */
    public function setShipmentDestinationData($value)
    {
        $this->_shipmentDestinationData = $value;

        return $this;
    }

    /**
     * Sets shipment origin country
     *
     * @param string $value
     * @return $this
     */
    public function setShipmentOriginCountry($value)
    {
        $this->_shipmentOriginCountry = $value;

        return $this;
    }

    /**
     * Sets shipping method
     *
     * @param string $shippingMethod
     * @return $this
     */
    public function setShippingMethod($shippingMethod)
    {
        $this->_shippingMethod = $shippingMethod;

        return $this;
    }
}
