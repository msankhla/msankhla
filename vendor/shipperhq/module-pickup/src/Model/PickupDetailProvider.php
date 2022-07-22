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
 * @package   ShipperHQ_Pickup
 * @copyright Copyright (c) 2016 Zowta LLC (http://www.ShipperHQ.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Pickup\Model;

use Magento\Framework\Session\SessionManagerInterface;

class PickupDetailProvider
{
    /**
     * @var SessionManagerInterface
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;
    /*
     * Map ShipperHQ detail key to Magento detail key
     */
    protected $valuesMap = [
        'pickup_location' => 'pickupName',
        'pickup_location_id' => 'pickupId',
        'pickup_latitude' => 'latitude',
        'pickup_longitude' => 'longitude',
        'pickup_email' => 'email',
        'pickup_contact' => 'contactName',
        'pickup_email_option' => 'emailOption'
    ];

    /**
     * @param SessionManagerInterface $checkoutSession
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     */
    public function __construct(
        SessionManagerInterface $checkoutSession,
        \Magento\Directory\Model\RegionFactory $regionFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->regionFactory = $regionFactory;
    }

    public function retrieveLocationDetails($locationId, $carrierCode, $carrierGroupId = false)
    {
        $found = false;
        $allDetails = $this->getSavedLocationsForCarrier($carrierCode, $carrierGroupId);
        if ($allDetails && is_array($allDetails)) {
            if (isset($allDetails[$locationId])) {
                $found = $allDetails[$locationId];
            }
        }
        return $found;
    }

    public function getSavedLocationsForCarrier($carrierCode, $carrierGroupId = false)
    {
        $found = false;
        $requestData = $this->checkoutSession->getShipperhqData();
        $allPickupDetails = isset($requestData['pickup_detail']) ? $requestData['pickup_detail'] : [];
        if ($carrierGroupId) {
            $found = (isset($allPickupDetails[$carrierGroupId]) && isset($allPickupDetails[$carrierGroupId][$carrierCode])) ?
                $allPickupDetails[$carrierGroupId][$carrierCode] : false;
        } else {
            foreach ($allPickupDetails as $carrierGroupID => $carrierCodeSet) {
                foreach ($carrierCodeSet as $oneCarrierCode => $details) {
                    if ($oneCarrierCode == $carrierCode) {
                        $found = $details;
                    }
                }
            }
        }
        return $found;
    }

    public function setSelectedLocationOnSession($locationId)
    {
        $requestData = $this->checkoutSession->getShipperhqData();
        $requestData['selected_location'] = $locationId;
        $this->checkoutSession->setShipperhqData($requestData);
    }

    public function getSelectedLocationOnSession()
    {
        $requestData = $this->checkoutSession->getShipperhqData();
        return isset($requestData['selected_location']) ? $requestData['selected_location'] : false;
    }

    /**
     * @return string | null
     */
    public function getRegion($countryId, $regionName)
    {
        $regionModel =  $this->regionFactory->create();
        $regionModel->loadByName($regionName, $countryId);
        $id =  $regionModel->getId() ? $regionModel->getId() : null;
        return $id;
    }

    public function getFormattedLocationDetailsFromLocationId($locationId, $carrierCode, &$additionalDetail)
    {
        if ($locationId) {
            $details = $this->getSavedLocationsForCarrier($carrierCode);
            if ($details && isset($details[$locationId])) {
                $fullDetails = $details[$locationId];
                $additionalDetail = $this->mapSavedDetails($fullDetails, $additionalDetail);
            }
        } else {
            $additionalDetail = $this->clearDownSavedDetails($additionalDetail);
        }
    }

    protected function mapSavedDetails($locationDetails, $additionalDetail)
    {
        foreach ($this->valuesMap as $setting => $retriever) {
            if (isset($locationDetails[$retriever])) {
                $additionalDetail->setData($setting, $locationDetails[$retriever]);
            }
        }
        return $additionalDetail;
    }

    protected function clearDownSavedDetails($additionalDetail)
    {
        foreach ($this->valuesMap as $setting => $retriever) {
            $additionalDetail->setData($setting, null);
        }
        return $additionalDetail;
    }
}
