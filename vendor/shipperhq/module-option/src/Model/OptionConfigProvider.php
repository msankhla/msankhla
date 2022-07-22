<?php
/**
 *
 * ShipperHQ
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Shipper HQ Shipping
 *
 * @category  ShipperHQ
 * @package   ShipperHQ_Option
 * @copyright Copyright (c) 2017 Zowta LLC (http://www.ShipperHQ.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Option\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class OptionConfigProvider implements ConfigProviderInterface
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    private static $defaultDisplayOptions = [
        'show_destination_type'         => false,
        'show_notify_required'          => false,
        'show_liftgate_required'        => false,
        'show_inside_delivery'          => false,
        'show_limited_delivery'         => false,
        'show_customer_carrier'         => false,
        'show_customer_carrier_ph'      => false,
        'show_customer_carrier_account' => false
    ];

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session            $checkoutSession
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
    ) {

        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->shipperLogger = $shipperLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $carrier = null;
        $quote = $this->checkoutSession->getQuote();
        $method = $quote->getShippingAddress()->getShippingMethod();
        if (isset($method) && strpos($method, '_') > 0) {
            $method = explode('_', $method);
            $carrier = $method[0];
        }
        return $this->getOptionConfig($carrier);
    }

    /**
     * Get the option config data based on selected shipping carrier
     *
     * @param $carrier
     * @return mixed
     */
    public function getOptionConfig($carrier = 'default')
    {
        $optionConfig['default'] = [
            'load_config_url'         => $this->getLoadConfigUrl(),
            'destination_type_values' => [],
        ];
        $optionConfig['default'] = array_merge($optionConfig['default'], self::$defaultDisplayOptions);

        $requestData = $this->checkoutSession->getShipperhqData();
        $allOptionDetails = isset($requestData['option_detail']) ? $requestData['option_detail'] : null;
        //To handle multiple option instances we can pass config such as this -
        // need to reference carrier group ID when we do split
        if (is_array($allOptionDetails)) {
            foreach ($allOptionDetails as $carrierGroupId => $carrierGroupCalDetails) {
                foreach ($carrierGroupCalDetails as $carrierCode => $carrierOptionDetails) {
                    $carrierOptionConfig = [
                        'load_config_url'   => $this->getLoadConfigUrl(),
                        'carrier_code'      => $carrierCode,
                        'carrier_id'        => $carrierOptionDetails['carrier_id'],
                        'carrier_group_id'  => $carrierGroupId
                    ];
                    $carrierOptionConfig = array_merge($carrierOptionConfig, self::$defaultDisplayOptions);
                    if (isset($carrierOptionDetails['formatedOptions'])) {
                        foreach ($carrierOptionDetails['formatedOptions'] as $code => $optionArray) {
                            $carrierOptionConfig[$code .'_values'] = (array)$optionArray[$code .'_values'];
                            $carrierOptionConfig[$code .'_default_value'] = strtolower($optionArray[$code .'_default_value']);
                            $carrierOptionConfig['show_' .$code] = true;
                            $carrierOptionConfig['show_option'] = true;
                        }
                    }
                    if (isset($requestData['checkout_selections'])) {
                        $carrierOptionConfig = $this->checkForExistingSelections(
                            $carrierCode,
                            $carrierOptionConfig,
                            $requestData['checkout_selections']
                        );
                    }
                    $optionConfig[$carrierCode] = $carrierOptionConfig;
                }
            }
        }

        if (!isset($optionConfig[$carrier])) {
            $this->shipperLogger->postDebug(
                'ShipperHQ Shipper',
                'Option Config Detail ',
                'Loading default configuration as no carrier details for carrier ' .$carrier
            );
            $carrier = 'default';
        }
        $this->shipperLogger->postDebug(
            'ShipperHQ Shipper',
            'Checkout Options - returning config to checkout for carrier ' .$carrier,
            $optionConfig[$carrier]
        );

        $config['shipperhq_option'] = $optionConfig[$carrier];
        return $config;
    }

    /**
     * Returns URL to controller action to refresh and load latest option configuration
     *
     * @return string
     */
    private function getLoadConfigUrl()
    {
        $store = $this->storeManager->getStore();
        return $store->getUrl('shipperhq_option/checkout/loadConfig', ['_secure' => $store->isCurrentlySecure()]);
    }

    private function checkForExistingSelections($carrierCode, $carrierOptionConfig, $checkoutSelections)
    {
        if ($checkoutSelections->getSelectedOptions() === null) {
            return $carrierOptionConfig;
        }
        if ($checkoutSelections->getCarrierCode() !== '' && $checkoutSelections->getCarrierCode() !== $carrierCode) {
            //different carrier
            return $carrierOptionConfig;
        }
        foreach ($checkoutSelections->getSelectedOptions() as $data) {
            //take value from selected options
            $carrierOptionConfig[$data['name'] .'_default_value'] = $data['value'];
        }
        return $carrierOptionConfig;
    }
}
