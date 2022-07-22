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
 * @package ShipperHQ_Calendar
 * @copyright Copyright (c) 2017 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\Pickup\Controller\Adminhtml\RequestRates;

use Magento\Framework\Json\Helper\Data;
use Psr\Log\LoggerInterface;
use ShipperHQ\Common\Model\Pickup;

/*
 * @deprecated
 */
class Index extends \Magento\Backend\App\Action
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
     * @var \ShipperHQ\Common\Model\Pickup
     */
    protected $pickupImpl;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $sessionQuote;
    /**
     * @var \ShipperHQ\Pickup\Model\PickupConfigProvider
     */
    protected $configProvider;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param Data $jsonHelper
     * @param Pickup $calendarImpl
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \ShipperHQ\Pickup\Model\PickupConfigProvider $configProvider
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        Data $jsonHelper,
        Pickup $pickupImpl,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \ShipperHQ\Pickup\Model\PickupConfigProvider $configProvider
    ) {
        $this->pickupImpl = $pickupImpl;
        $this->shipperLogger = $shipperLogger;
        $this->jsonHelper = $jsonHelper;
        $this->sessionQuote = $sessionQuote;
        $this->configProvider = $configProvider;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $this->shipperLogger->postDebug('Shipperhq_Pickup', 'Admin order request Rates action',$params);

        $carrierGroupId = $params['carriergroup_id'];
        $carrierCode = $params['carrier_code'];
        $selectedDate = $params['date_selected'];
        $carrierId = $params['carrier_id'];
        $locationId = $params['location_id'];
        $cartId = $this->sessionQuote->getQuote()->getId();

        $rates = $this->pickupImpl->processPickupAdminDateSelect($locationId, $selectedDate, $carrierId, $carrierCode, $carrierGroupId, $cartId);
        $updatedConfig = $this->configProvider->getPickupConfig($carrierCode, $selectedDate);
        $response = [
            'success' => true,
            'rates'  => $rates,
            'location_config' => $updatedConfig['shipperhq_pickup']
        ];
        $returnValues = $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );

        return $returnValues;
    }
}
?>