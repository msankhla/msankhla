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

use FedEx\CrossBorder\Helper\Data as Helper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Quote\Model\Quote\Item as QouteItem;
use Magento\Shipping\Model\CarrierFactory;

class DomesticCost
{
    const CONFIG_PATH_HUB       = 'fedex_crossborder/hub_address/';
    const CONFIG_PATH_CARRIER   = 'carriers/fdxcb/';

    /**
     * @var CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var RateRequestFactory
     */
    protected $_rateRequestFactory;

    /**
     * DomesticCost constructor.
     *
     * @param CarrierFactory $carrierFactory
     * @param CheckoutSession $checkoutSession
     * @param Helper $helper
     * @param RateRequestFactory $rateRequestFactory
     */
    public function __construct(
        CarrierFactory $carrierFactory,
        CheckoutSession $checkoutSession,
        Helper $helper,
        RateRequestFactory $rateRequestFactory
    ) {
        $this->_carrierFactory = $carrierFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_rateRequestFactory = $rateRequestFactory;
    }

    /**
     * @return Helper
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Returns domestic shipping cost
     *
     * @return float
     */
    public function getDomesticShippingCost()
    {
        $price = 0;
        if ($this->isActive()) {
            if (!$this->isFixed()) {
                $list = explode('_', $this->getHelper()->getConfig(static::CONFIG_PATH_CARRIER . 'domestic_shipping_method'));
                $carrierCode = (isset($list[0]) ? $list[0] : '');
                $methodCode = (isset($list[1]) ? $list[1] : '');
                if (!empty($carrierCode) && !empty($methodCode)) {
                    if ($carrier = $this->_carrierFactory->create($carrierCode)) {
                        $this->getHelper()->isDomesticShipping(true);
                        if ($rates = $carrier->collectRates($this->getRateRequest())) {
                            foreach ($rates->getAllRates() as $rate) {
                                if ($rate->getMethod() == $methodCode) {
                                    $price = $rate->getPrice();
                                    break;
                                }
                            }
                        }
                        $this->getHelper()->isDomesticShipping(false);
                    }
                }
            } else {
                $price = $this->getHelper()->getConfig(static::CONFIG_PATH_CARRIER . 'domestic_shipping_price');
            }
        }

        return (float) $price;
    }

    /**
     * Returns hub address value
     *
     * @param string $name
     * @return mixed
     */
    public function getHubValue($name)
    {
        return $this->getHelper()->getConfig(static::CONFIG_PATH_HUB . $name);
    }

    /**
     * Returns quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Returns rate request
     *
     * @return RateRequest
     */
    public function getRateRequest()
    {
        $shippingAddress = $this->getQuote()->getShippingAddress();
        $storeManager = $this->getHelper()->getStoreManager();
        
        /** @var $request \Magento\Quote\Model\Quote\Address\RateRequest */
        $request = $this->_rateRequestFactory->create();
        $request->setAllItems($shippingAddress->getAllItems());

        $street = $this->getHubValue('street_line1');
        $street .= trim((!empty($street) ? "\n" : '') . $this->getHubValue('street_line2'));
        $request->setDestCountryId(
            $this->getHubValue('country_id')
        )->setDestRegionId(
            $this->getHubValue('region_id')
        )->setDestPostcode(
            $this->getHubValue('postcode')
        )->setDestCity(
            $this->getHubValue('city')
        )->setDestStreet(
            $street
        );

        $request->setPackageValue($shippingAddress->getBaseSubtotal());
        $packageWithDiscount = $shippingAddress->getBaseSubtotalWithDiscount();
        $request->setPackageValueWithDiscount($packageWithDiscount);
        $request->setPackageWeight($shippingAddress->getWeight());
        $request->setPackageQty($shippingAddress->getItemQty());

        /**
         * Need for shipping methods that use insurance based on price of physical products
         */
        $packagePhysicalValue = $shippingAddress->getBaseSubtotal() - $shippingAddress->getBaseVirtualAmount();
        $request->setPackagePhysicalValue($packagePhysicalValue);

        $request->setFreeMethodWeight($shippingAddress->getFreeMethodWeight());

        /**
         * Store and website identifiers specified from StoreManager
         */
        if ($this->getQuote()->getStoreId()) {
            $storeId = $this->getQuote()->getStoreId();
            $request->setStoreId($storeId);
            $request->setWebsiteId($storeManager->getStore($storeId)->getWebsiteId());
        } else {
            $request->setStoreId($storeManager->getStore()->getId());
            $request->setWebsiteId($storeManager->getWebsite()->getId());
        }
        $request->setFreeShipping($shippingAddress->getFreeShipping());

        /**
         * Currencies need to convert in free shipping
         */
        $request->setBaseCurrency($storeManager->getStore()->getBaseCurrency());
        $request->setPackageCurrency($storeManager->getStore()->getCurrentCurrency());
        $request->setLimitCarrier($shippingAddress->getLimitCarrier());
        $baseSubtotalInclTax = $shippingAddress->getBaseSubtotalTotalInclTax();
        $request->setBaseSubtotalInclTax($baseSubtotalInclTax);

        return $request;
    }

    /**
     * Checks if domestic is active
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->getHelper()->getConfig(static::CONFIG_PATH_CARRIER . 'active');
    }

    /**
     * Checks if domestic cost is fixed
     *
     * @return bool
     */
    public function isFixed()
    {
        return (bool) !$this->getHelper()->getConfig(static::CONFIG_PATH_CARRIER . 'domestic_shipping');
    }
}