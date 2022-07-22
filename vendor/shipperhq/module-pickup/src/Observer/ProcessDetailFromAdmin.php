<?php
/**
 *
 * ShipperHQ Shipping Module
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
 * @package ShipperHQ_Shipping_Carrier
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Pickup\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * ShipperHQ Shipper module observer
 */
class ProcessDetailFromAdmin implements ObserverInterface
{
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /**
     * @var \ShipperHQ\Pickup\Model\PickupDetailProvider
     */
    private $pickupDetailProvider;

    /**
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param \ShipperHQ\Pickup\Model\PickupDetailProvider $pickupDetailProvider
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \ShipperHQ\Pickup\Model\PickupDetailProvider $pickupDetailProvider
    )
    {
        $this->shipperLogger = $shipperLogger;
        $this->pickupDetailProvider = $pickupDetailProvider;
    }

    /**
     * Process calendar fields on checkout
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {

        $orderData = $observer->getEvent()->getOrderData();
        $additionalDetail = $observer->getAdditionalDetail();
        $shippingAddress = $observer->getEvent()->getShippingAddress();

        //TODO - pass this along in observer
        //  $carrierGroupId = $observer->getCarrierGroupId();
        $locationId = false;
        if ($orderData) {
            $shippingMethod = $orderData['shipping_method'];
            list($carrierCode,$methodCode) = explode('_', $shippingMethod, 2);
            if(isset($orderData['location'])) {
                $locationId = $orderData['location'];
                $this->pickupDetailProvider->getFormattedLocationDetailsFromLocationId($locationId, $carrierCode, $additionalDetail);
                $carrierLocationDetails = $this->pickupDetailProvider->getSavedLocationsForCarrier($carrierCode);
                if(isset($carrierLocationDetails[$locationId])) {
                    $locationDetails = $carrierLocationDetails[$locationId];
                }
                else {
                    $locationDetails = $this->pickupDetailProvider->retrieveLocationDetails($locationId, $carrierCode);
                }

                if($locationDetails) {
                    $shippingAddress->setCity($locationDetails['city']);
                    $region = $this->pickupDetailProvider->getRegion($locationDetails['country'], $locationDetails['state']);
                    $shippingAddress->setRegionId($region);
                    $shippingAddress->setPostcode($locationDetails['zipcode']);
                    $shippingAddress->setCountryId($locationDetails['country']);
                    $shippingAddress->setCompany($locationDetails['pickupName']);
                    $street = $locationDetails['street2'] !== null ? $locationDetails['street1'] . ' ' .$locationDetails['street2'] :
                        $locationDetails['street1'];

                    $shippingAddress->setStreet($street);
                    $this->shipperLogger->postDebug('ShipperHQ Pickup',  'Admin order - updating shipping address for pickup '. $carrierCode, $locationDetails);
                }

            }
        }
        $this->shipperLogger->postDebug(
            'ShipperHQ Pickup',
            'Admin order fields saved',
            'Location ID: ' .$locationId
        );
        return $this;
    }


}
