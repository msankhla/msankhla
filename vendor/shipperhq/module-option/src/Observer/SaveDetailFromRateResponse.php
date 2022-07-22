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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * ShipperHQ Shipper module observer
 */
class SaveDetailFromRateResponse implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var\ShipperHQ\Common\Model\Option
     */
    protected $optionImpl;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \ShipperHQ\Common\Model\Option $optionImpl,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
    ) {
    
        $this->checkoutSession = $checkoutSession;
        $this->optionImpl = $optionImpl;
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
        //ignore if there are no available options or rates && it's not an customer account carrier
        if ((!isset($carrierRateResponse['availableOptions']) || $carrierRateResponse['availableOptions'] == ''
            || empty($carrierRateResponse['availableOptions'])|| !isset($carrierRateResponse['rates']))
            && $carrierRateResponse['carrierType'] != 'customerAccount'
			|| $carrierRateResponse['carrierType'] == 'shqshared') { //SHQ18-600
            return;
        }

        $carrierGroupDetail = $observer->getEvent()->getCarrierGroupDetail();

        $optionDetails = $this->optionImpl->processOptionDetails($carrierRateResponse, $carrierGroupDetail);
        
        $requestData = $this->checkoutSession->getShipperhqData();
        $this->shipperLogger->postDebug(
            'Shipperhq_Option',
            'Option Details saved ',
            $optionDetails
        );
        $allOptionDetails = isset($requestData['option_detail']) ? $requestData['option_detail'] : [];

        $allOptionDetails[$carrierGroupDetail['carrierGroupId']][$optionDetails['carrier_code']] = $optionDetails;
        $requestData['option_detail'] = $allOptionDetails;
        $this->checkoutSession->setShipperhqData($requestData);
    }
}
