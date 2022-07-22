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

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use ShipperHQ\Pickup\Model\PickupDetailProvider;
use ShipperHQ\Shipper\Helper\LogAssist;

/**
 * ShipperHQ Shipper module observer
 */
class ProcessDetailFromCheckout implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var LogAssist
     */
    private $shipperLogger;
    /**
     * @var PickupDetailProvider
     */
    private $pickupDetailProvider;

    //    protected $valuesMap = array(
    //        'pickup_location' => 'pickupName',
    //        'pickup_location_id' => 'pickupId',
    //        'pickup_latitude' => 'latitude',
    //        'pickup_longitude' => 'longitude',
    //        'pickup_email' => 'email',
    //        'pickup_contact' => 'contactName',
    //        'pickup_email_option' => 'emailOption'
    //    );

    /**
     * @param Session $checkoutSession
     * @param LogAssist $shipperLogger
     * @param PickupDetailProvider $pickupDetailProvider
     */
    public function __construct(
        Session $checkoutSession,
        LogAssist $shipperLogger,
        PickupDetailProvider $pickupDetailProvider
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->shipperLogger = $shipperLogger;
        $this->pickupDetailProvider = $pickupDetailProvider;
    }

    /**
     * Process calendar fields on checkout
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $addressExtensionAttributes = $observer->getEvent()->getAddressExtnAttributes();
        $additionalDetail = $observer->getAdditionalDetail();
        $carrierCode = $observer->getCarrierCode();

        $requestData = $this->checkoutSession->getShipperhqData();
        $pickupDetails = !empty($requestData) && array_key_exists('pickup_detail', $requestData) ? $requestData['pickup_detail'] : [];

        $carrierIsPickup = false;

        // MNB-212 Can't grab carrier type so loop through pickupDetails and see if selected carrier is present
        // All pickup carriers should be present in this array
        foreach ($pickupDetails as $pickupDetail) {
            if (array_key_exists($carrierCode, $pickupDetail)) {
                $carrierIsPickup = true;
                break;
            }
        }

        // TODO - pass this along in observer $carrierGroupId = $observer->getCarrierGroupId();
        $locationId = false;
        if ($addressExtensionAttributes) {
            $locationId = $addressExtensionAttributes->getLocationId();

            if (!$carrierIsPickup) {
                // MNB-212 Pass in false if not pickup carrier. This will wipe the pickup details from $additionalDetail
                $this->pickupDetailProvider->getFormattedLocationDetailsFromLocationId(false, $carrierCode, $additionalDetail);

                $this->shipperLogger->postDebug(
                    'ShipperHQ Pickup',
                    'Carrier Type is Not Pickup. Carrier code: ' . $carrierCode,
                    'Deleting Pickup Data From Quote Address'
                );
            } else {
                $this->pickupDetailProvider->getFormattedLocationDetailsFromLocationId($locationId, $carrierCode, $additionalDetail);

                $this->shipperLogger->postDebug(
                    'ShipperHQ Pickup',
                    'Checkout fields found. Saving Pickup Details to Quote Address',
                    'Location ID: ' . $locationId
                );
            }
        }

        $this->pickupDetailProvider->setSelectedLocationOnSession($locationId);

        return $this;
    }
}
