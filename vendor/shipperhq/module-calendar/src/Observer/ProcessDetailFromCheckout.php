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
 * ShipperHQ Shipper module observer
 */
class ProcessDetailFromCheckout implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
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
        \Magento\Checkout\Model\Session $checkoutSession,
        \ShipperHQ\Common\Model\Calendar $calendarImpl,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->calendarImpl = $calendarImpl;
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
        $addressExtensionAttributes = $observer->getEvent()->getAddressExtnAttributes();
        $additionalDetail = $observer->getAdditionalDetail();
        if ($addressExtensionAttributes) {
            $deliveryDate = $addressExtensionAttributes->getDeliveryDate();

            // MNB-712 Moved setData on additionalDetail within conditionals. Needed for M2.3.4+
            if ($deliveryDate) {
                $formattedDeliveryDate =  date('Y-m-d', strtotime($deliveryDate));
                if ($addressExtensionAttributes->getTimeSlot()) {
                    $timeSlot = $addressExtensionAttributes->getTimeSlot();
                    $additionalDetail->setData('time_slot', $timeSlot);
                }
                //SHQ16-2456
                $this->calendarImpl->saveDateSelectOnCheckoutProceed($deliveryDate, '', '', '');
                $additionalDetail->setData('delivery_date', $formattedDeliveryDate);
            }
        }

        $this->shipperLogger->postDebug(
            'ShipperHQ Calendar',
            'Checkout fields found',
            'Delivery Date: ' .$formattedDeliveryDate .' Time slot: '. $timeSlot
        );
        return $this;
    }
}
