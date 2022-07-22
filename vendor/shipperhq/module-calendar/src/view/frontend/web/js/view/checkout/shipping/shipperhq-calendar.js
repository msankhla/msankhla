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

define([
    'jquery',
    "underscore",
    'ko',
    'uiComponent',
    'mage/calendar',
    'ShipperHQ_Calendar/js/model/config',
    'ShipperHQ_Calendar/js/model/timeslot',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'mage/storage',
    'Magento_Checkout/js/checkout-data'
], function ($, _, ko, Component, calendar, calendarConfig, timeslot, quote, shippingService, resourceUrlManager, rateRegistry, storage, checkoutData) {
    'use strict';

    var myDate = ko.observable(null);
    var shouldShow = ko.computed(function () {
        return calendarConfig.show_calendar() && quote.shippingMethod() !== null; });
    var timeslots = ko.computed(function () {
        return timeslot.timeslots(); });
    var showTimeslots = ko.computed(function () {
        return calendarConfig.show_timeslots(); });
    var noSlots = ko.observable(timeslot.timeslots().length === 0);
    var shipping_method = null;
    var myTimeslot = ko.observable(null);
    var displayDate = ko.computed(function() {
        if (myDate() === null) {
            return 'Please select date';
        }
        return $.datepicker.formatDate(calendarConfig.datepickerFormat, myDate());
    });
    var showWidget = ko.observable(false);

    return Component.extend({
        defaults: {
            template: 'ShipperHQ_Calendar/checkout/shipping/shipperhq-calendar'
        },
        myDate: myDate,
        displayDate: displayDate,
        noSlots: noSlots,
        myTimeslot: myTimeslot,
        shouldShow: shouldShow,
        timeslots: timeslots,
        showTimeslots: showTimeslots,
        shipping_method: shipping_method,
        showWidget: showWidget,
        isCarrierCodeInThere: function (haystack, carrierCode) {
            var indexOfFoundRate = -1;
            var arrayLength = haystack.length;
            for (var i = 0; i < arrayLength; i++) {
                if (haystack[i].carrier_code === carrierCode) {
                    indexOfFoundRate = i;
                    break
                }
            }
            return indexOfFoundRate;
        },
        updateRates: function () {
            var self = this;

            //be careful as the datepicker can use the locale of browser for dates so include only date text
            var dateText = $('.shq_datepicker_inline').datepicker({dateFormat: calendarConfig.datepickerFormat}).val();
            //  ideally pass to a function here onChangeSelectedDate(carriergroupId, carriergroupInsert, carrierCode);
            shippingService.isLoading(true);
            //SHQ16-1770
            var address = quote.shippingAddress();
            var quoteId = quote.getQuoteId();

            var params = (resourceUrlManager.getCheckoutMethod() == 'guest') ? {cartId: quote.getQuoteId()} : {};
            var urls = {
                'guest': '/guest-carts/:cartId/guestrequest-calendar-rates',
                'customer': '/carts/mine/request-calendar-rates'
            };
            var serviceUrl = resourceUrlManager.getUrl(urls, params);
            var payload = JSON.stringify({
                date_shipping_information: {
                    carriergroup_id: '',
                    carrier_code: calendarConfig.carrier_code,
                    carrier_id: calendarConfig.carrier_id,
                    date_selected: dateText,
                    cart_id: quoteId,
                    address: {
                        'street': address.street,
                        'city': address.city,
                        'region_id': address.regionId,
                        'region': address.region,
                        'country_id': address.countryId,
                        'postcode': address.postcode,
                        'email': address.email
                    }
                }
            });

            storage.post(
                serviceUrl,
                payload,
                false
            ).done(
                function (result) {
                    var cacheKey = address.getKey();
                    var cache = rateRegistry.get(cacheKey);
                    //SHQ18-268
                    //if cache is null/empty as logged in and guest users have different cache key method ???
                    if (!cache) {
                        cacheKey = address.getCacheKey();
                        cache = rateRegistry.get(address.getCacheKey());
                    }

                    var newCache = [];
                    //now add all updated rates from results
                    for (var i = 0; i < result.length; i++) {
                        newCache.push(result[i]);
                    }

                    var selectedCarrierCode = calendarConfig.carrier_code;

                    // SHQ18-2332 : keep existing rates from  cache that are not being replaced
                    for (var i = 0; i < cache.length; i++) {
                        var carrierCodeInCache = cache[i].carrier_code;

                        // MNB-726 If carrier returns no rates, ensure we don't show old cached rates
                        // This is more of a belt and braces fix. Real fix is below
                        if (result.length === 0 && selectedCarrierCode === carrierCodeInCache) {
                            continue;
                        }

                        var foundRate = self.isCarrierCodeInThere(result, carrierCodeInCache);
                        //we didn't find this carrier in results so we are NOT replacing it, we need to add it back into the new cache
                        //using splice here to try and retain the initial order of rates displayed on checkout

                        if (foundRate == -1) {
                            newCache.splice(i, 0, cache[i]);
                        }
                    }

                    cache = newCache;

                    // MNB-726 If an error result is returned when a date is selected - E.g no valid rates for dates
                    // then set the error rate as the selected rate on the quote. Should show calendar with error
                    // and not allow proceed to payment
                    for (var i = 0; i < result.length; i++) {
                        if (result[i].error_message !== false) {
                            checkoutData.setSelectedShippingRate(result[i].carrier_code  + '_' + result[i].method_code);
                        }
                    }

                    //SHQ18-268 set the rates back on the registry
                    rateRegistry.set(cacheKey, cache);

                    shippingService.setShippingRates(cache);

                    var isPickup = selectedCarrierCode.indexOf("pickup") !== -1;
                    if (isPickup) {
                        //SHQ16-2041 trigger pickup to reload their config so they get the correct time slots
                        calendarConfig.processing = false;
                        shippingService.isLoading(false);
                    } else {
                        //Check for empty results - no rates!
                        if (result.length > 0 && quote.shippingMethod() != null) {
                            calendarConfig.reloadConfig(function () {
                                //handle additional logic here
                            }, quote.shippingMethod().carrier_code);
                        } else {
                            calendarConfig.reloadConfig(function () {
                                //handle additional logic here
                            }, selectedCarrierCode);
                        }
                    }
                }
            ).fail(
                function (response) {
                    //TODO something
                    // shippingService.setShippingRates([]);
                    // errorProcessor.process(response);
                }
            ).always(
                function () {
                    shippingService.isLoading(false);
                    calendarConfig.processing = false;
                }
            );
        },
        initialize: function () {
            this._super();

            var self = this;

            timeslot.timeslots.extend({ rateLimit: 50 });
            timeslot.timeslots.subscribe(function (newValue) {
                this.noSlots(timeslot.timeslots().length === 0);
            }, this);

            quote.shippingMethod.subscribe(function (newValue) {
                self.showWidget(false);
                if ((newValue === true) || (quote.shippingMethod() == null)) {
                    // no shipping method set yet, hide calendar
                    calendarConfig.show_calendar(false);
                    return; // and stop here
                }
                var ccode = newValue.carrier_code;
                var method = newValue.carrier_code + '_' + newValue.method_code;

                // SHQ18-2988 - ensure the value set is displayed
                var origVal = $('#shipperhq_calendar').val();
                // Set default display date to same as input value
                if (origVal != undefined && origVal != '') {
                    var dateObject = getDateInDateFormat(origVal);
                    myDate(dateObject);
                } else {
                    origVal = calendarConfig.date_selected();
                    $('#shipperhq_calendar').val(origVal);
                    var dateObject = getDateInDateFormat(origVal);
                    myDate(dateObject);
                }

                if (method === this.shipping_method) {
                    return;
                }
                this.shipping_method = method;
                var isPickup = ccode.indexOf("pickup") !== -1;
                if (isPickup) {
                    //select method is pickup so don't need to load calendar details SHQ16-1958
                    return;
                }
                // Shipping method set, reload calendar details
                calendarConfig.reloadConfig(function () {
                    //SHQ16-2041 default date will be set on calendar not min date
                    // calendarConfig.date_selected(calendarConfig.min_date);

                    var origVal = $('#shipperhq_calendar').val();
                    // Set default display date to same as input value
                    if (origVal != undefined && origVal != '') {
                        var dateObject = getDateInDateFormat(origVal);
                        myDate(dateObject);
                    } else {
                        origVal = calendarConfig.date_selected();
                        $('#shipperhq_calendar').val(origVal);
                        var dateObject = getDateInDateFormat(origVal);
                        myDate(dateObject);
                    }

                    //SHQ18-1249 Refresh the datepicker config for belt and braces
                    $('.shq_datepicker_inline').datepicker("refresh");

                }, quote.shippingMethod().carrier_code);

            }, this);

            shippingService.isLoading.subscribe(function (status) {
                if (calendarConfig.processing) {
                    return; // Don't do anything since we triggered the update
                }
                if (status === false) { // done loading rates
                    if (quote.shippingMethod() === null || !quote.shippingMethod().available) {
                        // no shipping method set yet
                        return; // stop here
                    }
                    calendarConfig.reloadConfig(function () {
                        // handle any calender detail updates needed here
                        var origVal = $('#shipperhq_calendar').val();
                        if (!origVal) {
                            $('#shipperhq_calendar').val(calendarConfig.min_date);
                            origVal = calendarConfig.min_date;
                        }
                        var dateObject = getDateInDateFormat(origVal);
                        // myDate(new Date(origVal));
                        myDate(dateObject);
                    }, quote.shippingMethod().carrier_code);
                }
            }, this);

            var updateTimeslots = function (newvalue) {
                if (newvalue === null) {
                    timeslot.setTimeslots([]);
                    return;
                }
                var tslots = timeslot.getTimeslotsForDate(newvalue);
                timeslot.setTimeslots(tslots);
            };
            updateTimeslots(calendarConfig.date_selected());

            var getDateInDateFormat = function(dateString) {
                if (calendarConfig.datepickerFormat === 'dd-mm-yy') {
                    var dateArr = dateString.split("-");
                    var day     = parseInt(dateArr[0], 10);
                    var month   = parseInt(dateArr[1], 10);
                    var year    = dateArr[2];
                    var date = new Date(year, month-1, day);

                    return date;
                } else {
                    var parts = dateString.split("/");
                    var date = new Date(parseInt(parts[2], 10),
                        parseInt(parts[0], 10) - 1,
                        parseInt(parts[1], 10));

                    return date;
                }
            };

            ko.subscribable.fn.subscribeChanged = function(callback) {
                if (!this.previousValueSubscription) {
                    this.previousValueSubscription = this.subscribe(function(_previousValue) {
                        this.previousValue = _previousValue;
                    }, this, 'beforeChange');
                }
                return this.subscribe(function(latestValue) {
                    callback(latestValue, this.previousValue);
                }, this);
            };

            this.myDate.subscribeChanged(function (newvalue, oldvalue) {
                if (calendarConfig.show_timeslots()) {
                    newvalue = $.datepicker.formatDate(calendarConfig.datepickerFormat, newvalue);
                    updateTimeslots(newvalue);
                }
            });

            calendarConfig.date_selected.subscribe(function (newvalue) {
                var currentDateFormatted = $.datepicker.formatDate(calendarConfig.datepickerFormat, this.myDate());
                if (currentDateFormatted !== newvalue && newvalue !== '') {
                    $('#shipperhq_calendar').datepicker("setDate", newvalue);
                    if( currentDateFormatted !== '') {
                        var dateObject = getDateInDateFormat(newvalue);
                        myDate(dateObject);
                    }
                    if (calendarConfig.show_timeslots()) {
                        updateTimeslots(newvalue);
                    }
                }
            }, this);

            ko.bindingHandlers.shq_timeslots = {
                init: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                    ko.utils.registerEventHandler(element, "change", function () {
                        myTimeslot(valueAccessor());
                    });
                }
            };

            ko.bindingHandlers.shq_datepicker_trigger = {
                init: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                    ko.utils.registerEventHandler(element, "click", function () {
                        showWidget(!showWidget());
                    });
                }
            };

            ko.bindingHandlers.shq_datepicker_value = {
                init: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                    ko.utils.registerEventHandler(element, "change", function () {
                        calendarConfig.processing = true;
                        var observable = valueAccessor();
                        observable($('.shq_datepicker_inline').datepicker("getDate"));

                        //we don't need to update here because we are observing the
                        //selection of a new date in on Select function
                        // self.updateRates();
                    });
                },
                update: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                    if (calendarConfig.processing) {
                        return; // Don't do anything since we triggered the update
                    }
                    if (!calendarConfig.show_calendar()) {
                        return;
                    }
                    var $el = $('.shq_datepicker_inline');
                    //load config
                    var dateselected = calendarConfig.date_selected();
                    if (dateselected === undefined || dateselected === null) {
                        dateselected = '';
                    }
                    var options = {
                        showOn: "focus",
                        altField: "#shipperhq_calendar",
                        buttonText: "",
                        dateFormat: calendarConfig.datepickerFormat,
                        minDate: calendarConfig.min_date,
                        maxDate: calendarConfig.max_date,
                        beforeShowDay: function (date) {
                            //ideally move to a function
                            var dmy = $.datepicker.formatDate(calendarConfig.datepickerFormat, date);
                            var alloweddates = calendarConfig.allowed_dates;
                            var found = [false, "", "unAvailable"];
                            for (var key in alloweddates) {
                                if (dmy === alloweddates[key]) {
                                    found = [true, "", "Available"];
                                    break;
                                }
                            }
                            return found;
                        },
                        onSelect: function (date) {
                            var dateObject = getDateInDateFormat(date);
                            myDate(dateObject);
                            showWidget(false);
                            self.updateRates();
                        }
                    };

                    $el.datepicker(options);
                    var dateText = getDateInDateFormat(dateselected);

                    $el.datepicker('setDate', dateText);

                    // self.updateRates();
                }
            };

            return this;
        }

    });

});
