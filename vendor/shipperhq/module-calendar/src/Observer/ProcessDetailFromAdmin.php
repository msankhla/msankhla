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

/**
 * ShipperHQ Calendar module observer
 */
class ProcessDetailFromAdmin implements ObserverInterface
{
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     */
    public function __construct(
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
    ) {
        $this->shipperLogger = $shipperLogger;
    }

    /**
     * Process calendar fields on checkout
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        //SHQ16-1958 save as null if none present in addtional detail to reset
        $formattedDeliveryDate = $timeSlot = null;
        $orderData = $observer->getEvent()->getOrderData();
        $additionalDetail = $observer->getAdditionalDetail();

        if ($orderData && isset($orderData['delivery_date'])) {
            $deliveryDate = $orderData['delivery_date'];

            if ($deliveryDate) {
                $formattedDeliveryDate =  date('Y-m-d', strtotime($deliveryDate));
                if (isset($orderData['timeslot'])) {
                    $timeSlot = $orderData['timeslot'];
                }
            }
        }
        $additionalDetail->setData('delivery_date', $formattedDeliveryDate);
        $additionalDetail->setData('time_slot', $timeSlot);
        $this->shipperLogger->postDebug(
            'ShipperHQ Calendar',
            'Admin order fields found',
            'Delivery Date: ' .$formattedDeliveryDate .' Time slot: '. $timeSlot
        );
        return $this;
    }
}
