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
 * @category ShipperHQ
 * @package ShipperHQ_Option
 * @copyright Copyright (c) 2017 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Shipping method management class for guest carts.
 */
namespace ShipperHQ\Option\Model;

use ShipperHQ\Common\Model\Option;

class OptionShippingProcessor
{
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /**
     * @var Data
     */
    protected $jsonHelper;
    /**
     * @var \ShipperHQ\Common\Model\Option
     */
    protected $optionImpl;

    private static $options = [
        'destination_type',
        'notify_required',
        'liftgate_required',
        'inside_delivery',
        'limited_delivery'
    ];
    /**
     * @param Context $context
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param Data $jsonHelper
     * @param \ShipperHQ\Common\Model\Option $optionImpl
     * @codeCoverageIgnore
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \ShipperHQ\Common\Model\Option $optionImpl
    ) {
        $this->optionImpl = $optionImpl;
        $this->shipperLogger = $shipperLogger;
    }

    public function requestShippingRates($cartId, $parameters)
    {
        //SHQ18-278 request rates for selected carrier
        $carrierGroupId = $parameters['carriergroup_id'];
        $carrierCode = $parameters['carrier_code'];
        $carrierId = $parameters['carrier_id'];
        $address = $parameters['address'];
        $optionValues =  [];

        foreach (self::$options as $option) {
            if (isset($parameters[$option]) && $parameters[$option] !== null) {
                $optionValues[$option] = $parameters[$option];
            }
        }
        $this->shipperLogger->postDebug('ShipperHQ Option', 'Requesting rates : parameters', $optionValues);
        $rates = $this->optionImpl->processOptionSelect(
            $optionValues,
            $carrierId,
            $carrierCode,
            $carrierGroupId,
            $address,
            $cartId
        );

        return $rates;
    }
}
