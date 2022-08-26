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
use FedEx\CrossBorder\Api\Data\ResultInterface;
use FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformationInterface;
use FedEx\CrossBorder\Api\Data\QuoteLinkInterface;
use FedEx\CrossBorder\Api\Data\QuoteLinkInterfaceFactory;
use FedEx\CrossBorder\Api\OrderLinkManagementInterface;
use FedEx\CrossBorder\Api\OrderManagementInterface;
use FedEx\CrossBorder\Api\TaxManagementInterface;
use FedEx\CrossBorder\Helper\Data as Helper;
use FedEx\CrossBorder\Model\Carrier\Shipping;
use FedEx\CrossBorder\Model\Payment\General as Payment;
use FedEx\CrossBorder\Model\Checkout\DomesticCost;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Directory\Model\ResourceModel\Currency as ResourceCurrency;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class OrderManagement implements OrderManagementInterface
{
    const ERROR_ORDER_EXIST             = 'The order for this quote already exist';
    const ERROR_INVALID_EMAIL           = 'Invalid email';
    const ERROR_INVALID_SHIPPING_METHOD = 'Invalid shipping method';
    const ERROR_NOT_EXIST               = 'The quote no longer exists';
    const ERROR_NO_PRODUCTS             = 'No products';
    const LOG_FILE                      = 'FedEx/CrossBorder/OrderCreator.log';

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var MerchantControl
     */
    protected $_merchantControl;

    /**
     * @var OrderInformationInterface
     */
    protected $_orderInformation;

    /**
     * @var OrderLinkManagementInterface
     */
    protected $_orderLinkManagement;

    /**
     * @var OrderSender
     */
    protected $_orderSender;

    /**
     * @var ProductCollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var Quote
     */
    protected $_quote;

    /**
     * @var QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var QuoteLinkInterfaceFactory
     */
    protected $_quoteLinkFactory;

    /**
     * @var QuoteManagement
     */
    protected $_quoteManagement;

    /**
     * @var RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var ResourceCurrency
     */
    protected $_resourceCurrency;

    /**
     * @var Result
     */
    protected $_result;

    /**
     * @var TaxManagementInterface
     */
    protected $_taxManagement;

    /**
     * @var array
     */
    protected $_mcServiceList = [
        3   => Shipping::METHOD_EXPRESS,
        2   => Shipping::METHOD_STANDARD,
        1   => Shipping::METHOD_ECONOMY,
    ];

    /**
     * @var array
     */
    protected $_serviceList = [
        0   => Shipping::METHOD_EXPRESS,
        1   => Shipping::METHOD_STANDARD,
        2   => Shipping::METHOD_ECONOMY,
    ];

    /**
     * OrderManagement constructor.
     *
     * @param CustomerFactory $customerFactory
     * @param MerchantControl $merchantControl
     * @param ProductCollectionFactory $productCollectionFactory
     * @param OrderLinkManagementInterface $orderLinkManagement
     * @param OrderSender $orderSender
     * @param QuoteFactory $quoteFactory
     * @param QuoteLinkInterfaceFactory $quoteLinkFactory
     * @param QuoteManagement $quoteManagement
     * @param RegionFactory $regionFactory
     * @param ResourceCurrency $resourceCurrency
     * @param Result $result
     * @param TaxManagementInterface $taxManagement
     */
    public function __construct(
        CustomerFactory $customerFactory,
        MerchantControl $merchantControl,
        ProductCollectionFactory $productCollectionFactory,
        OrderLinkManagementInterface $orderLinkManagement,
        OrderSender $orderSender,
        QuoteFactory $quoteFactory,
        QuoteLinkInterfaceFactory $quoteLinkFactory,
        QuoteManagement $quoteManagement,
        RegionFactory $regionFactory,
        ResourceCurrency $resourceCurrency,
        Result $result,
        TaxManagementInterface $taxManagement
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_merchantControl = $merchantControl;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_orderLinkManagement = $orderLinkManagement;
        $this->_orderSender = $orderSender;
        $this->_quoteFactory = $quoteFactory;
        $this->_quoteLinkFactory = $quoteLinkFactory;
        $this->_quoteManagement = $quoteManagement;
        $this->_regionFactory = $regionFactory;
        $this->_resourceCurrency = $resourceCurrency;
        $this->_result = $result;
        $this->_taxManagement = $taxManagement;
    }

    /**
     * Returns default company value
     *
     * @return string|null
     */
    protected function _getDefaultCompany()
    {
        return $this->getHelper()->getConfig(
            'fedex_crossborder/import_export/default_company'
        );
    }

    /**
     * Returns product by id
     *
     * @param mixed $productId
     * @return Product
     * @throws LocalizedException
     */
    protected function _getProduct($productId)
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect(
            '*'
        )->addFieldToFilter(
            $this->getHelper()->getProductIdentifier(),
            $productId
        );

        $product = $collection->getFirstItem();

        if (!$product || !$product->getId()) {
            throw new LocalizedException(__(
                'Product not found (%1 = %2)',
                $this->getHelper()->getProductIdentifier(),
                $productId
            ));
        }

        return $product;
    }

    /**
     * Returns region by id
     *
     * @param string|int $regionId
     * @return string
     */
    protected function _getRegionById($regionId)
    {
        if (!intval($regionId)) {
            return $regionId;
        }

        $region = $this->_regionFactory->create();
        $region->load((int) $regionId);

        return $region->getName();
    }

    /**
     * Prepares customer data
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareCustomer()
    {
        $email = $this->getOrderInformation()->getCustomerEmail();
        if (empty($email)) {
            throw new LocalizedException(__(static::ERROR_INVALID_EMAIL));
        }

        $websiteId = $this->getQuote()->getStore()->getWebsiteId();

        /** @var Customer $customer */
        $customer = $this->_customerFactory->create();
        $customer->setWebsiteId($websiteId)->loadByEmail($email);
        $this->getQuote()->setCustomerId(
            $customer->getId() ?: null
        )->setCustomerEmail(
            $email
        )->setCustomerIsGuest(
            !$customer->getId()
        )->setCustomerGroupId(
            $customer->getId() ? $customer->getGroupId() : GroupInterface::NOT_LOGGED_IN_ID
        );

        return $this;
    }

    /**
     * Prepare discount data
     *
     * @return $this
     */
    protected function _prepareDiscount()
    {
        $attributes = (array) $this->getOrderInformation()->getCustomAttributes();
        $code = (isset($attributes['custom_order2']) ? $attributes['custom_order2']->getValue() : '');
        if (!empty($code)) {
            $this->getQuote()->setCouponCode($code);
        }

        return $this;
    }

    /**
     * Prepares products data
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareProducts()
    {
        if (!count($this->getOrderInformation()->getProducts())) {
            $errorMessage = __(static::ERROR_NO_PRODUCTS);
            $this->addLog('[ERROR]: ' . $errorMessage);
            throw new LocalizedException($errorMessage);
        }

        $productList = [];
        foreach ($this->getOrderInformation()->getProducts() as $product) {
            $productList[$product->getProductId()] = (int) $product->getQty();
        }

        $code = $this->getHelper()->getProductIdentifier();
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($this->getQuote()->getAllVisibleItems() as $item) {
            $remove = false;
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $id = $child->getProduct()->getData($code);
                    $remove = !isset($productList[$id]);
                }
            } else {
                $id = $item->getProduct()->getData($code);
                $remove = !isset($productList[$id]);
            }

            if ($remove) {
                $this->getQuote()->removeItem(
                    $item->getItemId()
                );
            }
        }

        return $this;
    }

    /**
     * Prepares shipping address data
     *
     * @return $this
     */
    protected function _prepareShippingAddress()
    {
        $this->getQuote()->getShippingAddress(
        )->unsetData([
            'region_id',
            'customer_address_id'
        ])->addData(
            $this->getOrderInformation()->getShippingAddress()->getData()
        )->setSameAsBilling(false);

        if (!$this->getQuote()->getShippingAddress()->getCompany()) {
            $this->getQuote()->getShippingAddress()->setCompany(
                $this->_getDefaultCompany()
            );
        }

        return $this;
    }

    /**
     * Prepares billing address data
     *
     * @return $this
     */
    protected function _prepareBillingAddress()
    {
        $this->getQuote()->getBillingAddress(
        )->unsetData([
            'region_id',
            'customer_address_id'
        ])->addData(
            $this->getOrderInformation()->getBillingAddress()->getData()
        );

        if (!$this->getQuote()->getBillingAddress()->getCompany()) {
            $this->getQuote()->getBillingAddress()->setCompany(
                $this->_getDefaultCompany()
            );
        }

        return $this;
    }

    /**
     * Prepares shipping method data
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareShippingMethod()
    {
        $this->getQuote()->save()->collectTotals();
        $serviceId = (int) $this->getOrderInformation()->getShippingServiceId();
        $shippingAddress = $this->getQuote()->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
        $shippingMethod = Shipping::CODE . '_' . (
            $this->getMerchantControl()->isEnabled() ?
            $this->_mcServiceList[$serviceId] :
            $this->_serviceList[$serviceId]
        );

        $found = false;
        foreach ($shippingAddress->getShippingRatesCollection() as $rate) {
            if (!$rate->isDeleted() && $rate->getCode() === $shippingMethod) {
                $rate->setPrice($this->getOrderInformation()->getShippingCost() +
                    $this->getOrderInformation()->getShippingDomesticCost()+
                    $this->getOrderInformation()->getInsuranceCost()
                );
                $shippingDescription = $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle();
                $shippingAddress->setShippingDescription(trim($shippingDescription, ' -'));
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new LocalizedException(
                __(static::ERROR_INVALID_SHIPPING_METHOD)
            );
        }

        $shippingAddress->setShippingMethod(
            $shippingMethod
        );

        return $this;
    }

    /**
     * Prepares payment method data
     *
     * @return $this
     */
    protected function _preparePaymentMethod()
    {
        $this->getQuote()->setPaymentMethod(
            Payment::CODE
        )->setInventoryProcessed(
            false
        )->save();
        $this->getQuote()->getPayment()->importData([
            'method'    => Payment::CODE
        ]);


        /**
         * The email should be redefined after collectTotals as for some reason
         * after this method the customer email value is empty.
         * This is a temporary fix until we will not find another solution.
         */
        $this->getQuote(
        )->setTotalsCollectedFlag(
            false
        )->collectTotals(
        )->setCustomerEmail(
            $this->getOrderInformation()->getCustomerEmail()
        )->save();

        return $this;
    }

    /**
     * Prepares quote
     *
     * @return $this
     */
    protected function _prepareQuote()
    {
        $this->getQuote()->setCurrency();
        $this->_prepareCustomer();
        $this->_prepareProducts();
        $this->_prepareShippingAddress();
        $this->_prepareBillingAddress();
        //$this->_prepareDiscount();
        $this->_prepareShippingMethod();
        $this->_preparePaymentMethod();

        return $this;
    }

    /**
     * Prepare quote link
     *
     * @return $this
     */
    protected function _prepareQuoteLink()
    {
        $quoteId = $this->getQuote()->getId();
        /** @var QuoteLinkInterface $quoteLink */
        $quoteLink = $this->_quoteLinkFactory->create();
        $quoteLink->load(
            $quoteId,
            'quote_id'
        );
        if (!$quoteLink->getId()) {
            $quoteLink->setQuoteId(
                $this->getQuote()->getId()
            )->setFxcbOrderNumber(
                $this->getOrderInformation()->getOrderId()
            )->setTrackingLink(
                $this->getOrderInformation()->getTrackingLink()
            )->save();
        }

        return $this;
    }

    /**
     * Saving duty and tax
     *
     * @param float $dutyAmount
     * @param float $taxAmount
     * @return $this
     */
    protected function _saveDutyAndTax($dutyAmount, $taxAmount)
    {
        $this->getTaxManagement(
        )->setOrderDuty(
            $dutyAmount
        )->setOrderTax(
            $taxAmount
        );

        return $this;
    }

    /**
     * Updating currency rate
     *
     * @param string $currencyCode
     * @param float $currencyRates
     * @return $this
     */
    protected function _updateCurrencyRate($currencyCode, $currencyRates)
    {
        $currencyFrom = $this->getHelper()->getDefaultCurrency();
        if ($currencyFrom != $currencyCode) {
            $this->_resourceCurrency->getConnection()->insertOnDuplicate(
                $this->_resourceCurrency->getTable('directory_currency_rate'),
                [
                    'currency_from' => $currencyFrom,
                    'currency_to'   => $currencyCode,
                    'rate'          => $currencyRates
                ],
                ['rate']
            );
        }

        return $this;
    }

    /**
     * Replacing order shipping address to hub address
     * Only for non-merchant account
     *
     * @param OrderInterface $order
     * @return $this
     */
    protected function _updateShippingAddress(
        OrderInterface $order
    ) {
        if (!$this->getMerchantControl()->isEnabled()) {
            $order->getShippingAddress()->addData([
                'company'       => $this->getHubValue('company'),
                'country_id'    => $this->getHubValue('country_id'),
                'region'        => $this->_getRegionById($this->getHubValue('region_id')),
                'region_id'     => $this->getHubValue('region_id'),
                'postcode'      => $this->getHubValue('postcode'),
                'city'          => $this->getHubValue('city'),
                'street'        => implode("\n", [$this->getHubValue('street_line1'), $this->getHubValue('street_line2')]),
            ])->save();
        }

        return $this;
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
     * Create order
     *
     * @param OrderInformationInterface $orderInformation
     * @return ResultInterface
     */
    public function create(
        OrderInformationInterface $orderInformation
    )
    {
        $this->_result->reset();
        try {
            $this->_orderInformation = $orderInformation;
            $this->addLog('[DATA] Order data received: ' . print_r($this->_orderInformation->toArray(), true));

            $this->_updateCurrencyRate(
                $orderInformation->getCurrency(),
                $orderInformation->getCurrencyRate()
            )->_saveDutyAndTax(
                $orderInformation->getDutyCost(),
                $orderInformation->getTaxCost()
            )->_prepareQuote(
            )->_prepareQuoteLink(
            );

            $order = $this->_quoteManagement->submit($this->getQuote());
            $this->_orderLinkManagement->setFdxcbData($order);
            $this->_updateShippingAddress($order);
            $this->addLog('[SUCCESS]: Order Created (Order ID = ' . $order->getId() . '; Quote ID = ' . $this->getQuote()->getId() . ')');
        } catch (LocalizedException $e) {
            $this->_result->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->_result->addErrorMessage($e->getMessage());
        }

        if ($this->_result->getStatus() == Result::STATUS_ERROR) {
            $this->addLog('[ERROR]: ' . $this->_result->getMessage());
        }

        return $this->_result;
    }

    /**
     * @return Helper
     */
    public function getHelper()
    {
        return $this->getMerchantControl()->getHelper();
    }

    /**
     * Returns hub address value
     *
     * @param string $name
     * @return mixed|null
     */
    public function getHubValue($name)
    {
        return $this->getHelper()->getConfig(DomesticCost::CONFIG_PATH_HUB . $name);
    }

    /**
     * Returns merchant control model
     *
     * @return MerchantControl
     */
    public function getMerchantControl() {
        return $this->_merchantControl;
    }


    /**
     * Returns order information
     *
     * @return OrderInformationInterface
     */
    public function getOrderInformation()
    {
        return $this->_orderInformation;
    }

    /**
     * Returns quote
     *
     * @param int|null $quoteId
     * @return Quote
     * @throws LocalizedException
     */
    public function getQuote($quoteId = null)
    {
        if (!isset($this->_quote)) {
            if (!isset($quoteId)) {
                $attributes = (array) $this->getOrderInformation()->getCustomAttributes();
                $quoteId = (int) (isset($attributes['custom_order1']) ? $attributes['custom_order1']->getValue() : 0);
            }
            $this->_quote = $this->_quoteFactory->create();
            $this->_quote->loadByIdWithoutStore(
                $quoteId
            );

            if ($this->getHelper()->getStoreManager()->getStore()->getId() !== $this->_quote->getStoreId()) {
                $this->getHelper()->getStoreManager()->setCurrentStore(
                    $this->_quote->getStoreId()
                );
            }

            $this->getHelper()->saveSelectedCountry(
                $this->getOrderInformation()->getShippingAddress()->getCountryId(),
                false
            )->saveSelectedCurrency(
                $this->getOrderInformation()->getCurrency()
            );

            if (!$this->_quote->getId()) {
                $errorMessage = __(static::ERROR_NOT_EXIST);
            } elseif ($this->_quote->getReservedOrderId()) {
                $errorMessage = __(static::ERROR_ORDER_EXIST);
            }

            if (isset($errorMessage)) {
                $this->addLog('[WARNING]: ' . $errorMessage);
                throw new LocalizedException($errorMessage);
            }
        }

        return $this->_quote;
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
}
