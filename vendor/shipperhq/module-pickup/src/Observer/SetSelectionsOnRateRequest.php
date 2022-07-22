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
class SetSelectionsOnRateRequest implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $checkoutSession;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $checkoutSession
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $checkoutSession,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->shipperLogger = $shipperLogger;
    }

    /**
     * Add selected options to rate request
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $requestData = $this->checkoutSession->getShipperhqData();
        if(!isset($requestData['checkout_selections']) || !is_object($requestData['checkout_selections'])) {
            return;
        }
        $data = $requestData['checkout_selections'];
        if($data->getSelectedLocation()) {
            $request->setLocationSelected($data->getSelectedLocation());
        }
    }
}
