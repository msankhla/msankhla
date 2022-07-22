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
 * @category  ShipperHQ
 * @package   ShipperHQ_Calendar
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Calendar\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class CalendarConfigProvider implements ConfigProviderInterface
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var SessionManagerInterface;
     */
    protected $checkoutSession;
    /**
     * @var DateTime
     */
    protected $coreDate;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param SessionManagerInterface $checkoutSession
     * @param DateTime $coreDate
     * @param\ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        SessionManagerInterface $checkoutSession,
        DateTime $coreDate,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->_storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->coreDate = $coreDate;
        $this->shipperLogger = $shipperLogger;
        $this->localeDate = $localeDate;
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $carrier = null;
        //SHQ16-2219 removed logic to load carrier from quote shipping address here, should load default unless explicitly called
        return $this->getCalendarConfig($carrier);
    }

    /**
     * Get the calendar config data based on selected shipping carrier
     *
     * @param string $carrier
     * @param string $dateSelected
     * @param bool   $isAdmin
     *
     * @return mixed
     */
    public function getCalendarConfig($carrier = 'default', $dateSelected = '', $isAdmin = false)
    {
        if (str_contains($carrier ?? 'default', 'pickup')) {
            return [];
        }
        //SHQ16-2041 use locale default formats
        $defaultDateFormat = $this->localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $dateFormat = $this->dateFormatLookup($defaultDateFormat, 'date');

        $calendarConfig['default'] = [
            'dateFormat'        => $dateFormat, //'m/d/Y',
            'datepickerFormat'  => $this->dateFormatLookup($defaultDateFormat, 'datepicker'), //'mm/dd/yy',
            'min_date'          => $this->coreDate->date($dateFormat),
            'max_date'          => $this->coreDate->date($dateFormat, '+1 Year'),
            'date_selected'     => '',//SHQ16-2041 date_selected blank by default so calendar js set to min_date if blank
            'load_config_url'   => $this->getLoadConfigUrl(),
            'admin_request_rates_url' => $this->getAdminRequestRatesUrl(),
            'allowed_dates'     => [],
            'timeslots'         => [],
            'show_calendar'     => false,
            'show_timeslots'    => false
        ];
        $requestData = $this->checkoutSession->getShipperhqData();
        $allCalendarDetails = isset($requestData['calendar_detail']) ? $requestData['calendar_detail'] : null;
        $allAvailableDates = [];

        //To handle multiple calendar instances we can pass config such as this - need to reference carrier group ID when we do split
        if (is_array($allCalendarDetails)) {
            if ($isAdmin) {
                // MNB-1251 In admin there's only one calendar with no rate caching. Show all available dates from all carriers
                foreach ($allCalendarDetails as $carrierGroupCalDetails) {
                    foreach ($carrierGroupCalDetails as $carrierCalendarDetails) {
                        $allAvailableDates = array_unique(array_merge($allAvailableDates, $carrierCalendarDetails['allowed_dates']), SORT_REGULAR);
                    }
                }
            }

            foreach ($allCalendarDetails as $carrierGroupId => $carrierGroupCalDetails) {
                foreach ($carrierGroupCalDetails as $carrierCode => $carrierCalendarDetails) {
                    //SHQ16-2041 - confirm default date
                    $defaultDate = $carrierCalendarDetails['default_date'] ?? $dateSelected;

                    $carrierCalendarConfig = [
                        'load_config_url'   => $this->getLoadConfigUrl(),
                        'admin_request_rates_url' => $this->getAdminRequestRatesUrl(),
                        'dateFormat'        => $carrierCalendarDetails['dateFormat'],
                        'datepickerFormat'  => $carrierCalendarDetails['datepickerFormat'],
                        'min_date'          => $carrierCalendarDetails['min_date'],
                        'max_date'          => $carrierCalendarDetails['max_date'],
                        'allowed_dates'     => $isAdmin ? $allAvailableDates : $carrierCalendarDetails['allowed_dates'],
                        'date_selected'     => $defaultDate, //$carrierCalendarDetails['default_date'],
                        'carrier_code'      => $carrierCode,
                        'carrier_id'        => $carrierCalendarDetails['carrier_id'],
                        'carrier_group_id'  => $carrierGroupId,
                        'timeslots'         => $carrierCalendarDetails['display_time_slots'],
                        'show_timeslots'    => $carrierCalendarDetails['showTimeslots']
                    ];
                    //SHQ16-2285 default config is not set from the specific carrier
                    $carrierCalendarConfig['show_calendar'] = true;
                    $calendarConfig[$carrierCode] = $carrierCalendarConfig;
                }
            }
        }

        if (!isset($calendarConfig[$carrier])) {
            $this->shipperLogger->postDebug('ShipperHQ Shipper', 'Calendar Config Detail ', 'Loading default configuration as no carrier details for carrier ' . $carrier);
            $carrier = 'default';
        }

        $config['shipperhq_calendar'] = $calendarConfig[$carrier];
        $this->shipperLogger->postDebug('ShipperHQ Shipper', 'Calendar Config Detail ', $config['shipperhq_calendar']);
        return $config;
    }

    /**
     * Returns URL to controller action to refresh and load latest calendar configuration
     *
     * @return string
     */
    protected function getLoadConfigUrl()
    {
        $store = $this->_storeManager->getStore();
        return $store->getUrl('shipperhq_calendar/checkout/loadConfig', ['_secure' => $store->isCurrentlySecure()]);
    }

    /**
     * Returns URL to controller action to request shipping rates for selected date
     *
     * @return string
     */
    protected function getAdminRequestRatesUrl()
    {
        return $this->url->getUrl('shipperhq_calendar/requestRates');
    }

    private function dateFormatLookup($format, $type)
    {
        $this->shipperLogger->postDebug('ShipperHQ Shipper', 'Calendar Config Detail ', 'looking for ' . $format);
        $lookup = [
            'dd/MM/y' => ['date' => 'd-m-Y', 'datepicker' => 'dd-mm-yy'],
            'dd/MM/yyyy' => ['date' => 'd-m-Y', 'datepicker' => 'dd-mm-yy'], // SHQ2117 added to account for dateFormat changes on M2.3
            'd/MM/yy' => ['date' => 'd-m-Y', 'datepicker' => 'dd-mm-yy'],
            'M/d/yy' => ['date' => 'm/d/Y', 'datepicker' => 'mm/dd/yy'],
            'default' => ['date' => 'm/d/Y', 'datepicker' => 'mm/dd/yy']
        ];

        if (isset($lookup[$format]) && isset($lookup[$format][$type])) {
            return $lookup[$format][$type];
        }

        return $lookup['default'][$type];
    }
}
