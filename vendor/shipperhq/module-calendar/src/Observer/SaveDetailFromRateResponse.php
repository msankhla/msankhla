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

namespace ShipperHQ\Calendar\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * ShipperHQ Shipper module observer
 */
class SaveDetailFromRateResponse implements ObserverInterface
{
    /**
     * @var SessionManagerInterface
     */
    protected $checkoutSession;
    /*
     * @var \ShipperHQ\Common\Model\Calendar
     */
    protected $calendarImpl;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        SessionManagerInterface $checkoutSession,
        \ShipperHQ\Common\Model\Calendar $calendarImpl,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->calendarImpl = $calendarImpl;
        $this->shipperLogger = $shipperLogger;
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
        if (!isset($carrierRateResponse['calendarDetails']) || $carrierRateResponse['calendarDetails'] == ''
            || !isset($carrierRateResponse['rates'])) {
            return;
        }

        $carrierGroupDetail = $observer->getEvent()->getCarrierGroupDetail();

        $calendarDetails = $this->calendarImpl->processCalendarDetails($carrierRateResponse, $carrierGroupDetail);

        $requestData = $this->checkoutSession->getShipperhqData();

        if ($requestData && isset($requestData['checkout_selections']) && is_object($requestData['checkout_selections'])) {
            $data = $requestData['checkout_selections'];
            if ($data->getSelectedDate() && $data->getSelectedDate() != $calendarDetails['default_date']) {
                //not sure that we should be resetting or not?
//                $calendarDetails['default_date'] = $defaultDate;
            }
        }
        $this->shipperLogger->postDebug('Shipperhq_Calendar', 'Calendar Details saved ', 'Carrier: ' . $calendarDetails['carrier_code'] . ' and carrier group ' . $carrierGroupDetail['carrierGroupId']);
        $allCalendarDetails = isset($requestData['calendar_detail']) ? $requestData['calendar_detail'] : [];

        $allCalendarDetails[$carrierGroupDetail['carrierGroupId']][$calendarDetails['carrier_code']] = $calendarDetails;
        $requestData['calendar_detail'] = $allCalendarDetails;
        $this->checkoutSession->setShipperhqData($requestData);
    }
}
