<?php
/**
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Orderview
 * @copyright Copyright (c) 2019 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Orderview\ViewModel;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;

class Orderview implements ArgumentInterface
{
    const SHIPPERHQ_ORDERVIEW_ENDPOINT_PATH = 'carriers/shqserver/orderview_url';
    const SHIPPERHQ_ORDERVIEW_BUNDLE_URL = 'carriers/shqserver/orderview_bundle_url';

    /** @var array */
    private $config;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var Registry */
    private $registry;

    /**
     * Orderview constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Registry $registry
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
        $this->initializeConfig();
    }

    private function initializeConfig() {

        $order = $this->getOrder();
        $storeId = $order->getStoreId();
        $endpoint = $this->scopeConfig->getValue(self::SHIPPERHQ_ORDERVIEW_ENDPOINT_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $orderview_bundle_url = $this->scopeConfig->getValue(self::SHIPPERHQ_ORDERVIEW_BUNDLE_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $secret_token = '';
        $environment_scope = '';

        // This is a tricky bit of code.  Basically it pulls all carriers/shqserver/* configs and makes them into
        // local variables
        if ($this->scopeConfig->isSetFlag('carriers/shqserver/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)) {
            extract($this->scopeConfig->getValue('carriers/shqserver', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId), EXTR_IF_EXISTS);
        } elseif ($this->scopeConfig->isSetFlag('carriers/shipper/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)) {
            extract($this->scopeConfig->getValue('carriers/shipper', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId), EXTR_IF_EXISTS);
        }

        $this->config['orderview'] = [
            "endpoint" => $endpoint,
            "bundleUrl" => $orderview_bundle_url,
            "secretToken" => $secret_token,
            "scope" => $environment_scope,
            "currency" => $order->getOrderCurrencyCode(),
        ];
    }


    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function getSerializedConfig()
    {
        return json_encode($this->getConfig(), JSON_HEX_TAG);
    }

    /**
     * Retrieve current order
     *
     * @return Order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getOrder()
    {
        if ($this->registry->registry('current_order')) {
            return $this->registry->registry('current_order');
        }
        if ($this->registry->registry('order')) {
            return $this->registry->registry('order');
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t get the order instance right now.'));
    }
}
