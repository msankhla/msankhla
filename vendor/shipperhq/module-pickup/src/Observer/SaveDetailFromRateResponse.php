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
use Magento\Framework\Message\ManagerInterface;


/**
 * ShipperHQ Shipper module observer
 */
class SaveDetailFromRateResponse implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $checkoutSession;
    /*
     * @var \ShipperHQ\Common\Model\Pickup
     */
    protected $pickupImpl;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    private $shipperDataHelper;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \ShipperHQ\Common\Model\Pickup $pickupImpl
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $checkoutSession,
        \ShipperHQ\Common\Model\Pickup $pickupImpl,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->pickupImpl = $pickupImpl;
        $this->shipperLogger = $shipperLogger;
        $this->shipperDataHelper = $shipperDataHelper;
    }

    /**
     * Update saved shipping methods available for ShipperHQ
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $carrierRateResponse = $observer->getEvent()->getCarrierRateResponse();
        if(!isset($carrierRateResponse['pickupLocationDetails']) || $carrierRateResponse['pickupLocationDetails'] == ''
            || !isset($carrierRateResponse['rates'])) {
            return;
        }

        $carrierGroupDetail = $observer->getEvent()->getCarrierGroupDetail();
        $globalSettings = $this->shipperDataHelper->getGlobalSettings();
        $carrierGroupDetail['distanceUnit'] = (isset($globalSettings) && isset($globalSettings['distanceUnit'])) ?
            $globalSettings['distanceUnit'] : 'MI';
        $pickupDetails = $this->pickupImpl->processPickupDetails($carrierRateResponse, $carrierGroupDetail);

        $requestData = $this->checkoutSession->getShipperhqData();

        if($requestData && isset($requestData['checkout_selections']) && is_object($requestData['checkout_selections'])) {
            $data = $requestData['checkout_selections'];
            //TODO verify data against selected?
        }
        $this->shipperLogger->postDebug('Shipperhq_Pickup', 'Pickup Details saved ','Carrier code ' .$carrierRateResponse['carrierCode'] .' and carrier group ' .$carrierGroupDetail['carrierGroupId']);
        $allPickupDetails = isset($requestData['pickup_detail']) ? $requestData['pickup_detail'] : array();
        $allPickupDetails[$carrierGroupDetail['carrierGroupId']][$carrierRateResponse['carrierCode']] = $pickupDetails;
        $requestData['pickup_detail'] = $allPickupDetails;
        $this->checkoutSession->setShipperhqData($requestData);

    }
}
