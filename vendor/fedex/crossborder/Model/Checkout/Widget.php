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

use FedExCrossBorder\Auth\Credentials;
use FedExCrossBorder\Auth\OAuthClient;
use FedExCrossBorder\WebApp\Entity\Address;
use FedExCrossBorder\WebApp\Entity\Cart;
use FedExCrossBorder\WebApp\Entity\Customer;
use FedExCrossBorder\WebApp\Entity\Merchant;
use FedExCrossBorder\WebApp\Entity\Order;
use FedExCrossBorder\WebApp\Entity\Product;
use FedExCrossBorder\WebApp\WebAppClient;
use FedEx\Core\Model\Log;
use FedEx\CrossBorder\Model\MerchantControl;
use FedEx\CrossBorder\Model\ProductValidator;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class Widget
{
    const API_URL_PATH      = 'fedex_crossborder/api/';
    const LOG_FILE          = 'FedEx/CrossBorder/Checkout.log';
    const ERROR_LOG         = 'Error [%s]: %s';
    const ERROR_NOT_FOUND   = 'No available products';
    const ERROR_PRODUCT_ID  = 'The product identifier is empty';
    const ERROR_VALIDATION  = 'Error: One or more products are invalid';

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var DomesticCost
     */
    protected $_domesticCost;

    /**
     * @var ImageHelper
     */
    protected $_imageHelper;

    /**
     * @var bool
     */
    protected $_isValid;

    /**
     * @var MerchantControl
     */
    protected $_merchantControl;

    /**
     * @var array
     */
    protected $_products;

    /**
     * @var ProductValidator
     */
    protected $_productValidator;

    /**
     * Widget constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param DomesticCost $domesticCost
     * @param ImageHelper $imageHelper
     * @param MerchantControl $merchantControl
     * @param ProductValidator $productValidator
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        DomesticCost $domesticCost,
        ImageHelper $imageHelper,
        MerchantControl $merchantControl,
        ProductValidator $productValidator
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_domesticCost = $domesticCost;
        $this->_merchantControl = $merchantControl;
        $this->_imageHelper = $imageHelper;
        $this->_productValidator = $productValidator;
    }

    /**
     * Adds error log for product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    protected function _addErrorProductLog(\Magento\Catalog\Model\Product $product)
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
     * Converting products data into products
     *
     * @param array $list
     * @return $this
     */
    protected function _convertToProducts($list)
    {
        $this->_products = [];

        if (is_array($list)) {
            foreach ($list as $item) {
                $this->_products[] = $this->getProduct($item);
            }
        }

        return $this;
    }

    /**
     * Converting quote item element into product
     *
     * @param QuoteItem $item
     * @return array
     */
    protected function _itemToProduct(QuoteItem $item)
    {
        $code = $this->getHelper()->getProductIdentifier();
        $data = [
            'id'                => $item->getProduct()->getData($code),
            'name'              => $item->getProduct()->getName(),
            'country_of_origin' => $item->getProduct()->getFdxCountryOfOrigin(),
            'qty'               => $item->getQty(),
            'image'             => $this->_imageHelper->init(
                                        $item->getProduct(),
                                        'product_thumbnail_image'
                                    )->setImageFile(
                                        $item->getProduct()->getSmallImage()
                                    )->resize(
                                        55,
                                        58
                                    )->getUrl(),
        ];

        if ($item->getParentItemId()) {
            $parent = $item->getParentItem();
            $data['qty'] = $data['qty'] * $parent->getQty();

            switch ($parent->getProductType()) {
                case 'configurable':
                    $data['retail_price'] = $parent->getPrice();
                    $data['unit_price'] = ($parent->getBaseRowTotal() - $parent->getBaseDiscountAmount()) / $data['qty'];
                    break;
                default:
                    $data['retail_price'] = $item->getPrice();
                    $data['unit_price'] = ($item->getBaseRowTotal() - $item->getBaseDiscountAmount()) / $data['qty'];
            }
        } else {
            $data['retail_price'] = $item->getPrice();
            $data['unit_price'] = ($item->getBaseRowTotal() - $item->getBaseDiscountAmount()) / $data['qty'];
        }

        $this->addLog('Adding product: ' . json_encode($data));

        return $data;
    }

    /**
     * Merging product data
     *
     * @param array $list
     * @param array $product
     * @return $this
     */
    protected function _mergeProducts(&$list, $product)
    {
        $id = $product['id'];
        if (isset($list[$id])) {
            $list[$id]['qty'] += $product['qty'];
        } else {
            $list[$id] = $product;
        }

        return $this;
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
     * Returns billing address
     *
     * @return Address
     */
    public function getBillingAddress()
    {
        $billingAddress = new Address();
        $billingAddress->setCountry(
            $this->getHelper()->getSelectedCountry()
        );

        return $billingAddress;
    }

    /**
     * Returns cart
     *
     * @return Cart
     */
    public function getCart()
    {
        $this->addLog('Creating new Cart');

        $cart = new Cart();
        $cart->setMerchant(
            $this->getMerchant()
        )->setOrder(
            $this->getOrder()
        )->setCustomer(
            $this->getCustomer()
        )->setProducts(
            $this->getProducts()
        );

        $this->addLog('Cart created successfully');
        $this->addLog($cart->toJSON(JSON_PRETTY_PRINT));

        return $cart;
    }

    /**
     * Returns customer
     *
     * @return Customer
     */
    public function getCustomer()
    {
        $customer = new Customer();
        $customer->setBilling(
            $this->getBillingAddress()
        );

        return $customer;
    }

    /**
     * Returns domain
     *
     * @return string
     */
    public function getDomain()
    {
        return parse_url($this->getHelper()->getOrderConfirmationUrl(), PHP_URL_HOST);
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
     * Returns helper
     *
     * @return \FedEx\CrossBorder\Helper\Data
     */
    public function getHelper()
    {
        return $this->_productValidator->getHelper();
    }

    /**
     * Returns merchant
     *
     * @return Merchant
     */
    public function getMerchant()
    {
        $merchant = new Merchant();
        $merchant->setPartnerKey(
            $this->getHelper()->getPartnerKey()
        )->setCallbackUrl(
            $this->getHelper()->getOrderConfirmationUrl()
        );

        if ($this->isMerchantEnabled()) {
            $merchant->setMerchantControl(true);
            $merchant->fcb_shipping_methods = !$this->_merchantControl->customShippingRates();
            $merchant->custom_shipping_rates = $this->_merchantControl->customShippingRates();
        }

        return $merchant;
    }

    /**
     * Returns order
     *
     * @return Order
     */
    public function getOrder()
    {
        $order = new Order();
        $order->setOrderCurrency(
            $this->getHelper()->getDefaultCurrency()
        )->setCustomOrder1(
            $this->getQuote()->getId()
        )->setCustomOrder2(
            $this->getQuote()->getCouponCode()
        )->setDomesticShippingCharge(
            $this->getDomesticShippingCost()
        );

        return $order;
    }

    /**
     * Returns product
     *
     * @param array $data
     * @return Product
     */
    public function getProduct($data)
    {
        $product = new Product();
        $product->setId(
            $data['id']
        )->setName(
            $data['name']
        )->setUnitPrice(
            $data['unit_price']
        )->setRetailPrice(
            $data['retail_price']
        )->setCountryOfOrigin(
            $data['country_of_origin']
        )->setQuantity(
            $data['qty']
        )->setImage(
            $data['image']
        );

        return $product;
    }

    /**
     * Returns products
     *
     * @return Product[]
     */
    public function getProducts()
    {
        if (!isset($this->_products)) {
            $this->addLog('Preparing Products...');

            $this->_isValid = true;
            $productList = [];
            /** @var \Magento\Quote\Model\Quote\Item $item */
            foreach ($this->getQuoteItems() as $item) {
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        if ($this->_productValidator->isProductAvailable($child->getProduct())) {
                            $this->_mergeProducts(
                                $productList,
                                $this->_itemToProduct($child)
                            );
                        } else {
                            $this->_addErrorProductLog($child->getProduct());
                            $this->_isValid = false;
                        }
                    }
                } elseif ($this->_productValidator->isProductAvailable($item->getProduct())) {
                    $this->_mergeProducts(
                        $productList,
                        $this->_itemToProduct($item)
                    );
                } else {
                    $this->_addErrorProductLog($item->getProduct());
                    $this->_isValid = false;
                }
            }

            $this->_convertToProducts($productList);

            $this->addLog('Found ' . count($this->_products) . ' product(s)');
        }

        return $this->_products;
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
     * Returns all quote items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function getQuoteItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Returns API secure url
     *
     * @return string
     */
    public function getSecureUrl()
    {
        return $this->getHelper()->getConfig(static::API_URL_PATH . 'secure_url');
    }

    /**
     * Returns API url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getHelper()->getConfig(static::API_URL_PATH . 'webapp_url');
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

    /**
     * Checks if available products exist
     *
     * @return bool
     */
    public function isProductsExist()
    {
        return count($this->getProducts()) > 0;
    }

    /**
     * Returns widget code
     *
     * @return string
     */
    public function toHtml()
    {
        if ($this->isProductsExist()) {
            if ($this->_isValid) {
                try {
                    $credentials = new Credentials(
                        $this->getHelper()->getApiClientId(),
                        $this->getHelper()->getApiClientSecret(),
                        $this->getHelper()->getPartnerKey()
                    );
                    $oauthClient = new OAuthClient(
                        $credentials,
                        $this->getSecureUrl()
                    );
                    $access_token = $oauthClient->clientCredentials();

                    $webAppClient = new WebAppClient([
                        'headers' => [
                            'domain' => $this->getDomain(),
                        ],
                    ],
                        $this->getUrl()
                    );
                    $webAppClient->setAccessToken(
                        $access_token->getAccessToken()
                    );

                    return $webAppClient->getWidget(
                        $this->getCart()
                    );
                } catch (\FedExCrossBorder\Exception\HttpException $exception) {
                    $this->addLog(sprintf(
                        static::ERROR_LOG,
                        $exception->getCode(),
                        $exception->getMessage()
                    ));
                } catch (\Exception $exception) {
                    $this->addLog(sprintf(
                        static::ERROR_LOG,
                        $exception->getCode(),
                        $exception->getMessage()
                    ));
                }
            } else {
                $this->addLog(static::ERROR_VALIDATION);
            }
        } else {
            $this->addLog(sprintf(
                static::ERROR_LOG,
                404,
                static::ERROR_NOT_FOUND
            ));
        }

        return '';
    }
}
