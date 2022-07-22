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
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\Calendar\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\Helper\Data;
use Psr\Log\LoggerInterface;
use ShipperHQ\Common\Model\Calendar;

class LoadConfig extends Action
{
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /**
     * @var Data
     */
    protected $jsonHelper;
    /*
     * @var \ShipperHQ\Calendar\Model\CalendarConfigProvider
     */
    protected $calendarConfigProvider;


    /**
     * @param Context $context
     * @param Sidebar $sidebar
     * @param LoggerInterface $logger
     * @param Data $jsonHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        Data $jsonHelper,
        \ShipperHQ\Calendar\Model\CalendarConfigProvider $calendarConfigProvider
    ) {
        $this->calendarConfigProvider = $calendarConfigProvider;
        $this->shipperLogger = $shipperLogger;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $carrier = $this->getRequest()->getParam('carrier', 'default');
        $dateSelected = $this->getRequest()->getParam('date_selected', 'default');
        $config = $this->calendarConfigProvider->getCalendarConfig($carrier, $dateSelected);

        $response = [
            'success' => true,
            'config'  => $config
        ];
        $returnValues = $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );

        return $returnValues;
    }
}
