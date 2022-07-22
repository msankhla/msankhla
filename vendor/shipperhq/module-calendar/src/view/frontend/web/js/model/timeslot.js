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
 * @copyright Copyright (c) 2016 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
define(
    ['ko', 'ShipperHQ_Calendar/js/model/config'],
    function (ko, calendarConfig) {
        'use strict';
        var timeslots = ko.observableArray([]);
        return {
            timeslots: timeslots,
            //SHQ16-2162 moved declaration
            rawData: window.checkoutConfig.shipperhq_calendar.timeslots,
            getTimeslotsForDate: function (date) {
                for (var slot in this.rawData) {
                    if (slot === date) {
                        var value = this.rawData[slot];
                        return value;
                    }
                }
                return [];
            },
            setTimeslots: function (tslots) {
                this.timeslots([]);
                for (var key in tslots) {
                    //SHQ16-2162 - prevent loading prototype object properties
                    if (tslots.hasOwnProperty(key)) {
                        var slot = {
                            key: key,
                            value: tslots[key]
                        };
                        this.timeslots().push(slot);
                    }
                }
                return this.timeslots();
            }

        };
    }
);
