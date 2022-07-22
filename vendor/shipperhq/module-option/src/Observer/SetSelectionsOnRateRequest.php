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
 * @package ShipperHQ_Option
 * @copyright Copyright (c) 2017 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Option\Observer;

use Braintree\Exception;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * ShipperHQ Shipper module observer
 */
class SetSelectionsOnRateRequest implements ObserverInterface
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
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
    ) {
    
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

        if (!isset($requestData['checkout_selections']) || !is_object($requestData['checkout_selections'])) {
            $this->shipperLogger->postDebug(
                'ShipperHQ Options',
                'Checkout selections are empty',
                'returning'
            );

            return $this;
        }
        $data = $requestData['checkout_selections'];

        if ($data->getSelectedOptions()) {
            $request->setSelectedOptions($this->mergeOptions($request->getSelectedOptions(), $data->getSelectedOptions()));
        }

        if ($data->getCarrierGroupId()) {
            $request->setCarriergroupId($data->getCarrierGroupId());
        }

        if ($data->getCarrierId()) {
            $request->setCarrierId($data->getCarrierId());
        }

    }

    private function mergeOptions($existing, $customerSelected)
    {
        $finalOptions = $customerSelected;
        //need to deterimine if we have destination type result set in both
        $customerDestinationType = $existingDestinationType = false;
        foreach ($customerSelected as $selectedData) {
            if($selectedData['name'] === 'destination_type') {
                $customerDestinationType = $selectedData['value'];
            }
        }

        foreach ($existing as $existingData) {
            if($existingData['name'] === 'destination_type') {
                $existingDestinationType = $existingData['value'];
            }
        }

        //SHQ16-2266
        //in this case we are saying the destination type selected and displayed, is the value used in request, otherwise the validated value is used
        if (!$customerDestinationType && $existingDestinationType) {
            $finalOptions[] = ['name' => 'destination_type', 'value' => $existingDestinationType];
        }

        $this->shipperLogger->postDebug(
            'ShipperHQ Options',
            'Existing options were',
            $existing
        );
        $this->shipperLogger->postDebug(
            'ShipperHQ Options',
            'Merging options on rate request',
            $finalOptions
        );

        return $finalOptions;
    }
}
