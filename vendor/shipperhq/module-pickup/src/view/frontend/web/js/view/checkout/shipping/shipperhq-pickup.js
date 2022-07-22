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
    'ShipperHQ_Pickup/js/model/config',
    'ShipperHQ_Calendar/js/model/config',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service',
    'ShipperHQ_Shipper/js/lib/featherlight.min',
    'ShipperHQ_Shipper/js/lib/featherlight.gallery.min'
], function ($, _, ko, Component, pickupConfig, calendarConfig, quote, shippingService) {
    'use strict';

    var myLocation = ko.observable(null);
    var shouldShow = ko.computed(function () {
        return pickupConfig.show_locations() && quote.shippingMethod() !== null;
    });
    var locations = ko.observableArray(pickupConfig.getLocations());
    var shipping_method = null;
    var mapShowing = ko.observable(true);
    var mapLinkLabel = ko.computed(function() {
        if (mapShowing()) {
            return 'Hide Map';
        }
        return 'Show Map';
    });

    return Component.extend({
        defaults: {
            template: 'ShipperHQ_Pickup/checkout/shipping/shipperhq-pickup'
        },
        myLocation: myLocation,
        shouldShow: shouldShow,
        locations: locations,
        mapShowing: mapShowing,
        mapLinkLabel: mapLinkLabel,
        shipping_method: shipping_method,
        showAddress: function() {
            return myLocation().showAddress !== 'hidden';
        },
        showMapInline: function() {
            if (!myLocation().api_key) {
                return false;
            }
            if (myLocation().showMap === 'hidden') {
                return false;
            }
            if (myLocation().showMap === 'popup') {
                return false;
            }
            return true;
        },
        toggleMap: function() {
            if ((myLocation().showMap === 'hidden') || !myLocation().api_key) {
                return false;
            }
            mapShowing(!mapShowing());
            return mapShowing();
        },
        getMap: function() {
            if (!this.showMapInline()) {
                return '';
            }
            return '<iframe' +
                '  width="100%"' +
                '  height="200"' +
                '  frameborder="0" style="border:0"' +
                '  src="' + this.getMapUrl() + '" allowfullscreen>' +
                '</iframe>'
        },
        getMapUrl: function() {
            return 'https://www.google.com/maps/embed/v1/search?key=' + myLocation().api_key + '&q=' + myLocation().mapAddress;
        },
        hideLocations: function () {
            this.locations([]);
            pickupConfig.show_locations(false);
        },
        isSelectedLocation: function (key) {
            if (myLocation() === null) {
                return false;
            }
            return myLocation().key === key;
        },
        selectLocation: function (locationId) {
            if (!locationId) {
                return;
            }
            if (typeof locationId === 'object') {
                locationId = ko.unwrap(locationId).key;
            }
            var locationArray = ko.unwrap(locations());
            for (var id in locationArray) {
                var location = locations()[id];
                if (location.key == locationId) {
                    myLocation(location);
                }
            }
        },
        initialize: function () {
            this._super();

            var self = this;
            var refreshLocations = function () {
                var inputId = $('input[name="shipperhq_location"]');
                var selectedLocation = inputId.val();
                self.locations(pickupConfig.getLocations()); //SHQ16-2041 this resets locations to first in list
                var found = false;
                self.locations().forEach(function(location) {
                    if (location.key.toString() === selectedLocation) {
                        found = true;
                    }
                }, found);

                if (!found && self.locations()[0]) {
                    selectedLocation = self.locations()[0].key;
                }
                inputId.val(selectedLocation); //SHQ16-2041 manually set to selected locations

                 self.selectLocation(inputId.val());
                $('.shq-map-popup').featherlight({
                    configuration: {
                        contentFilters: 'html',
                        html: ''
                    }
                });
            };

            quote.shippingMethod.subscribe(function (newValue) {
               
                if ((newValue === true) || (newValue === null) || (quote.shippingMethod() === null) || (newValue.available == false)) {
                    // no shipping method set yet, hide calendar
                    self.hideLocations();
                    return; // and stop here
                }
                var method = newValue.carrier_code + '_' + newValue.method_code;
                if (method === this.shipping_method) {
                    return;
                }
                this.shipping_method = method;
                // Shipping method set, reload calendar details
                pickupConfig.reloadConfig(refreshLocations, newValue.carrier_code);
            }, this);

            shippingService.isLoading.subscribe(function (status) {


                if (calendarConfig.processing) {
                    return; // Don't do anything since we triggered the update
                }
                if (status === false) { // done loading rates
                    if (quote.shippingMethod() === null || !quote.shippingMethod().available) {
                        // no shipping method set yet
                        self.hideLocations();
                        return; // stop here
                    }
                    pickupConfig.reloadConfig(refreshLocations, quote.shippingMethod().carrier_code);
                }
            });

            self.myLocation.subscribe(function (location) {
                if (typeof location === 'undefined' || location === null || location === "") {
                    return;
                }
                $('input[name="shipperhq_location"]').val(location.key);
                var calendarDetails = pickupConfig.getCalendarDetailsForLocation(location.key);
                if (calendarDetails === false || calendarDetails.length < 1) { // Must have been an invalid location
                    return;
                }

                calendarConfig.updateConfig(calendarDetails);

                //SHQ18-2971 Refresh datepicker. Available dates might have changed on change location
                $('.shq_datepicker_inline').datepicker("refresh");

                //  calendarConfig.date_selected(calendarConfig.min_date); SHQ16-2041 calendar config handles date setting
            });
            ko.bindingHandlers.shq_pickup = {
                init: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                    ko.utils.registerEventHandler(element, "change", function () {
                        // self.selectLocation($(element).val());
                    });

                    // Manually call this on initial load
                    //TODO (GE) I think this is wrong. we don't need to refresh locations.
                   // refreshLocations();

                },
                update: function (element, valueAccessor, allBindings, viewModel, bindingContext) {

                    // self.selectLocation(ko.unwrap(valueAccessor()));
                }
            };
            return this;
        }
    });
});


