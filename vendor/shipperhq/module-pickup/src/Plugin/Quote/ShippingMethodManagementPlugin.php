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

namespace ShipperHQ\Pickup\Plugin\Quote;

use Magento\Quote\Model\ShippingMethodManagement;

/**
 * ShipperHQ Shipper module observer
 */
class ShippingMethodManagementPlugin
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /**
     * @var \ShipperHQ\Pickup\Model\PickupDetailProvider
     */
    private $pickupDetailProvider;
    /*
     * \ShipperHQ\Shipper\Helper\Data
     */
    private $shipperDataHelper;
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param \ShipperHQ\Pickup\Model\PickupDetailProvider $pickupDetailProvider
     * @param \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \ShipperHQ\Pickup\Model\PickupDetailProvider $pickupDetailProvider,
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->shipperLogger = $shipperLogger;
        $this->pickupDetailProvider = $pickupDetailProvider;
        $this->shipperDataHelper = $shipperDataHelper;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Assign Shipping Address
     *
     * @param ShippingMethodManagement $subject
     * @param                                               $result
     * @param                                               $cartId
     * @param                                               $carrierCode
     * @param                                               $methodCode
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterApply(ShippingMethodManagement $subject, $result, $cartId, $carrierCode, $methodCode)
    {
        $quote = $this->quoteRepository->getActive($cartId);
        $shippingAddress = $quote->getShippingAddress();

        try {
            $carrierLocationDetails = $this->pickupDetailProvider->getSavedLocationsForCarrier($carrierCode);
            $originalAddress = $this->shipperDataHelper->getOriginalShippingAddress($quote);

            //confirm that this quote is pickup & get the location details
            if ($carrierLocationDetails && is_array($carrierLocationDetails)) {
                $locationId = $this->pickupDetailProvider->getSelectedLocationOnSession();

                if ($locationId) {
                    if (isset($carrierLocationDetails[$locationId])) {
                        $locationDetails = $carrierLocationDetails[$locationId];
                    } else {
                        $locationDetails = $this->pickupDetailProvider->retrieveLocationDetails($locationId, $carrierCode);
                    }

                    if ($locationDetails) {
                        // MNB-2230 Store the actual shipping address so we can revert to it if pickup is deselected
                        if (!$originalAddress) {
                            $clonedAddress = clone $shippingAddress;
                            $clonedAddress->setAddressType('shq_orig');
                            $quote->addAddress($clonedAddress);
                        }

                        $shippingAddress->setCity($locationDetails['city']);
                        $regionId = $this->pickupDetailProvider->getRegion($locationDetails['country'], $locationDetails['state']);
                        $shippingAddress->setRegionId($regionId);
                        if ($regionId == null) {$shippingAddress->setRegion($locationDetails['state']);}
                        $shippingAddress->setPostcode($locationDetails['zipcode']);
                        $shippingAddress->setCountryId($locationDetails['country']);
                        $shippingAddress->setCompany($locationDetails['pickupName']);
                        $street = $locationDetails['street2'] !== null ? $locationDetails['street1'] . ' ' . $locationDetails['street2'] :
                            $locationDetails['street1'];

                        $shippingAddress->setStreet($street);
                        $this->shipperLogger->postDebug('ShipperHQ Pickup', 'updating shipping address for pickup ' . $carrierCode, $locationDetails);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->shipperLogger->postCritical(
                'ShipperHQ Pickup',
                'Exception during shipping address modification',
                $e->getMessage()
            );
        }
    }
}
