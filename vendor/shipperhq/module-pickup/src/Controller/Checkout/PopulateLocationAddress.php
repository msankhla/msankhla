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
 * @package ShipperHQ_Pickup
 * @copyright Copyright (c) 2016 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\Pickup\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\Helper\Data;
use Psr\Log\LoggerInterface;

class PopulateLocationAddress extends Action
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
     * @var \ShipperHQ\Pickup\Model\PickupDetailProvider
     */
    private $pickupDetailProvider;


    /**
     * @param Context $context
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param Data $jsonHelper
     * @param \ShipperHQ\Pickup\Model\PickupDetailProvider $pickupDetailProvider
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        Data $jsonHelper,
        \ShipperHQ\Pickup\Model\PickupDetailProvider $pickupDetailProvider
    ) {
        $this->pickupDetailProvider = $pickupDetailProvider;
        $this->shipperLogger = $shipperLogger;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $carrierCode = $this->getRequest()->getParam('carrier', 'default');
        $locationId = $this->getRequest()->getParam('location_id');
        $addressDetails = false;
        if($locationId) {
            $locationDetails = $this->pickupDetailProvider->retrieveLocationDetails($locationId, $carrierCode);
            $this->shipperLogger->postDebug('ShipperHQ Pickup', 'PopulateLocationAddress - retrieving location address ' . $carrierCode . ' and ' . $locationId, $locationDetails);


            if ($locationDetails) {
                $addressDetails = [];
                $addressDetails['city'] = $locationDetails['city'];
                $region = $this->pickupDetailProvider->getRegion($locationDetails['country'], $locationDetails['state']);
                $addressDetails['region_id'] = $region;
                $addressDetails['region'] = $locationDetails['state'];
                $addressDetails['postcode'] = $locationDetails['zipcode'];
                $addressDetails['country'] = $locationDetails['country'];
                $addressDetails['company'] = $locationDetails['pickupName'];
                $addressDetails['street1'] = $locationDetails['street1'];
                $addressDetails['street2'] = $locationDetails['street2'];
                $addressDetails['location_address'] = $this->formatPickupForDisplay($locationDetails);
            }

        }
        $response = [
            'success' => true,
            'address'  => $addressDetails
        ];
        $returnValues = $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );

        return $returnValues;
    }

    private function formatPickupForDisplay($locationDetails)
    {
        $result =  $locationDetails['pickupName'] ? $locationDetails['pickupName'] : '';
        if ($locationDetails['street1']) {
            $result .=  ': ' .$locationDetails['street1'];
            if($locationDetails['city']) {
                $result .= ', ' .$locationDetails['city'];
            }
        }
        return $result;
    }
}
?>