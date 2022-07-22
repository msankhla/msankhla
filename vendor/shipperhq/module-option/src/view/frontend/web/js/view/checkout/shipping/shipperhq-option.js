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
 * @package ShipperHQ_Option
 * @copyright Copyright (c) 2017 Zowta LLC (http://www.ShipperHQ.com)
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
    'ShipperHQ_Option/js/model/config',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'mage/storage'
], function ($, _, ko, Component, optionConfig, quote, shippingService, resourceUrlManager, rateRegistry, storage) {
    'use strict';
    var shouldShow = ko.computed(function () {
        return optionConfig.show_option(); });
    var shouldShowDestinationType = ko.computed(function () {
        return optionConfig.show_destination_type(); });
    var shouldShowNotifyRequired = ko.computed(function () {
        return optionConfig.show_notify_required(); });
    var shouldShowLimitedDelivery = ko.computed(function () {
        return optionConfig.show_limited_delivery(); });
    var shouldShowInsideDelivery = ko.computed(function () {
        return optionConfig.show_inside_delivery(); });
    var shouldShowLiftgateRequired = ko.computed(function () {
        return optionConfig.show_liftgate_required(); });
    var shouldShowCustomerCarrier = ko.computed(function () {
        return optionConfig.show_customer_carrier(); });
    var shouldShowCustomerCarrierPh = ko.computed(function () {
        return optionConfig.show_customer_carrier_ph(); });
    var shouldShowCustomerCarrierAccount = ko.computed(function () {
        return optionConfig.show_customer_carrier_account(); });

    var shipping_method = null;
    var myAddressType = ko.observable(optionConfig.destination_type_default_value());
    var destination_type_values = ko.observableArray(optionConfig.getAddressTypes());
    var isLimitedDelivery = ko.observable(false);
    var isNotifyRequired = ko.observable(false);
    var isLiftgateRequired = ko.observable(false);
    var isInsideDelivery = ko.observable(false);
    var getCustomerCarrier = ko.observable();
    var ignoreChange = false;

    return Component.extend({
        defaults: {
            template: 'ShipperHQ_Option/checkout/shipping/shipperhq-option'
        },
        myAddressType: myAddressType,
        shouldShow: shouldShow,
        shouldShowDestinationType: shouldShowDestinationType,
        shouldShowNotifyRequired: shouldShowNotifyRequired,
        shouldShowInsideDelivery: shouldShowInsideDelivery,
        shouldShowLiftgateRequired: shouldShowLiftgateRequired,
        shouldShowLimitedDelivery: shouldShowLimitedDelivery,
        shouldShowCustomerCarrier: shouldShowCustomerCarrier,
        shouldShowCustomerCarrierPh: shouldShowCustomerCarrierPh,
        shouldShowCustomerCarrierAccount: shouldShowCustomerCarrierAccount,
        destination_type_values: destination_type_values,
        isLimitedDelivery: isLimitedDelivery,
        isNotifyRequired: isNotifyRequired,
        isInsideDelivery: isInsideDelivery,
        isLiftgateRequired: isLiftgateRequired,
        // getCustomerCarrier: getCustomerCarrier,
        shipping_method: shipping_method,
        ignoreChange: ignoreChange,
        hideOption: function () {
            this.destination_type_values([]);
            optionConfig.show_destination_type(false);
            optionConfig.show_limited_delivery(false);
            optionConfig.show_notify_required(false);
            optionConfig.show_liftgate_required(false);
            optionConfig.show_inside_delivery(false);
            optionConfig.show_customer_carrier(false);
            optionConfig.show_customer_carrier_ph(false);
            optionConfig.show_customer_carrier_account(false);
        },
        initialize: function () {
            this._super();

            var self = this;
            optionConfig.processing = false

            var refreshOption = function () {
                // MNB-2639 Need to ensure we're always setting the address type dropdown values
                self.ignoreChange = true;
                self.destination_type_values(optionConfig.getAddressTypes());

                // MNB-2354 prevent reloading config of accessorial options on checkout when clicking proceed
                if (optionConfig.processing) {
                    self.ignoreChange = false;
                    return; // Don't do anything since we triggered the update
                }

                // SHQ16-2298 ignore request rates if we are updating config values
                self.myAddressType(optionConfig.destination_type_default_value());
                isLimitedDelivery(scrubValue(optionConfig.limited_delivery_default_value()));
                isNotifyRequired(scrubValue(optionConfig.notify_required_default_value()));
                isLiftgateRequired(scrubValue(optionConfig.liftgate_required_default_value()));
                isInsideDelivery(scrubValue(optionConfig.inside_delivery_default_value()));
                self.ignoreChange = false;
            };

            quote.shippingMethod.subscribe(function (newValue) {
                if ((newValue === true) || (newValue == null) || (quote.shippingMethod() == null)) {
                    // no shipping method set yet, hide option
                    self.hideOption();
                    return; // and stop here
                }
                var method = newValue.carrier_code + '_' + newValue.method_code;
                if (method == this.shipping_method) {
                    return;
                }
                this.shipping_method = method;
                // Shipping method set, reload option details
                optionConfig.reloadConfig(refreshOption, newValue.carrier_code);
            }, this);

            shippingService.isLoading.subscribe(function (status) {
                if (optionConfig.processing) {
                    return; // Don't do anything since we triggered the update
                }

                if (status == false) { // done loading rates
                    if (quote.shippingMethod() == null) {
                        // no shipping method set yet
                        self.hideOption();
                        return; // stop here
                    }
                     optionConfig.reloadConfig(refreshOption, quote.shippingMethod().carrier_code);
                }
            });

            var scrubValue = function (value) {
                if (value === '1' || value === 1) {
                    return true;
                }

                if (value === '0' || value === 0) {
                    return false;
                }

                return value;
            };

            var getAllOptionValues = function () {

                var returnValues = [];
                var values = {
                    'destination_type' : myAddressType(),
                    'inside_delivery' : isInsideDelivery(),
                    'liftgate_required' : isLiftgateRequired(),
                    'limited_delivery' : isLimitedDelivery(),
                    'notify_required': isNotifyRequired()
                };

                $("[name=shipperhq-option]").each(function () {
                    var code = this.id.replace('shipperhq_', '');
                    var value = values[code];
                    if (this.type == 'checkbox') {
                        value = (value ? "1" : "0");
                    }
                    if (value !== '') {
                        returnValues[code] = value;
                    }
                });

                return returnValues;
            };

            myAddressType.subscribe(function (newvalue) {
                if (this.ignoreChange) {
                    return;
                }
                handleElementChanges();
            }, this);

            isLiftgateRequired.subscribe(function (newvalue) {
                if (this.ignoreChange) {
                    return;
                }
                handleElementChanges();
            }, this );

            isNotifyRequired.subscribe(function (newvalue) {
                if (this.ignoreChange) {
                    return;
                }
                handleElementChanges();
            }, this );

            isInsideDelivery.subscribe(function (newvalue) {
                if (this.ignoreChange) {
                    return;
                }
                handleElementChanges();
            }, this );

            isLimitedDelivery.subscribe(function (newvalue) {
                if (this.ignoreChange) {
                    return;
                }
                handleElementChanges();
            }, this );

            var getIndexOfRate = function (haystack, carrierCode, methodCode) {
                var indexOfFoundRate = -1;
                var arrayLength = haystack.length;
                for (var i = 0; i < arrayLength; i++) {
                    if (haystack[i].carrier_code === carrierCode && haystack[i].method_code == methodCode) {
                        indexOfFoundRate = i;
                        break;
                    }
                }
                return indexOfFoundRate;
            };

            var handleElementChanges = function() {
                optionConfig.processing = true;
                shippingService.isLoading(true);
                var address = quote.shippingAddress();
                var quoteId = quote.getQuoteId();
                var params = (resourceUrlManager.getCheckoutMethod() == 'guest') ? {cartId: quote.getQuoteId()} : {};
                var urls = {
                    'guest': '/guest-carts/:cartId/guestrequest-option-rates',
                    'customer': '/carts/mine/request-option-rates'
                };
                var serviceUrl = resourceUrlManager.getUrl(urls, params);
                var allOptionValues = getAllOptionValues();
                var payload = JSON.stringify({
                    option_shipping_information: {
                        carriergroup_id: '',
                        carrier_code: optionConfig.carrier_code,
                        carrier_id: optionConfig.carrier_id,
                        destination_type: allOptionValues['destination_type'],
                        inside_delivery: allOptionValues['inside_delivery'],
                        liftgate_required: allOptionValues['liftgate_required'],
                        limited_delivery: allOptionValues['limited_delivery'],
                        notify_required: allOptionValues['notify_required'],
                        cart_id: quoteId,
                        address: {
                            'street': address.street,
                            'city': address.city,
                            'region_id': address.regionId,
                            'region': address.region,
                            'country_id': address.countryId,
                            'postcode': address.postcode,
                            'email': address.email,
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
                        //SHQ18-278
                        //if cache is null/empty as logged in and guest users have different cache key method ???
                        if (!cache) {
                            cacheKey = address.getCacheKey();
                            cache = rateRegistry.get(address.getCacheKey());
                        }
                        //SHQ18-278 manipulate the cache to replace the rate with updated
                        //SHQ18-1011 Handle scenario when fedex_ground and ground_home_delivery replace each other
                        var selectedMethod = quote.shippingMethod();
                        var newSelected = selectedMethod;
                        for (var i = 0; i < result.length; i++) {
                            var foundRateIdx = getIndexOfRate(cache, result[i].carrier_code, result[i].method_code);
                            if (foundRateIdx < 0) {
                                if (result[i].method_code === 'FEDEX_GROUND') {
                                    foundRateIdx = getIndexOfRate(cache, result[i].carrier_code, 'GROUND_HOME_DELIVERY');
                                    newSelected = (
                                        selectedMethod
                                        && foundRateIdx >= 0
                                        && selectedMethod.carrier_code === result[i].carrier_code
                                        && selectedMethod.method_code === 'GROUND_HOME_DELIVERY'
                                    ) ? result[i] : selectedMethod
                                } else if (result[i].method_code === 'GROUND_HOME_DELIVERY') {
                                    foundRateIdx = getIndexOfRate(cache, result[i].carrier_code, 'FEDEX_GROUND')
                                    newSelected = (
                                        selectedMethod
                                        && foundRateIdx >= 0
                                        && selectedMethod.carrier_code === result[i].carrier_code
                                        && selectedMethod.method_code === 'FEDEX_GROUND'
                                    ) ? result[i] : selectedMethod
                                }
                            }

                            if (foundRateIdx > -1) {
                                cache[foundRateIdx] = result[i];
                            }
                        }
                        //SHQ18-1011 explicitly set the shipping method for the fedex case of home delivery
                        if (newSelected) {
                            this.shipping_method = newSelected;
                            quote.shippingMethod(newSelected)
                        }

                        //SHQ18-278 set the rates back on the registry
                        rateRegistry.set(cacheKey, cache);
                        shippingService.setShippingRates(cache);

                    }
                ).fail(
                    function (response) {
                        // shippingService.setShippingRates([]);
                        // errorProcessor.process(response);
                    }
                ).always(
                    function () {
                        shippingService.isLoading(false);
                        optionConfig.processing = false;
                    }
                );
            };

            ko.bindingHandlers.shq_option = {
                init: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                    //SHQ16-2298 moved this code to a handler on each observable
                    // as it was more reliable to retrieve all values before requesting rates

                    // ko.utils.registerEventHandler(element, "change", function () {
                    //
                    // });
                },
                update: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
                    if (optionConfig.processing) {
                        return; // Don't do anything since we triggered the update
                    }
                    if (!optionConfig.show_destination_type()) {
                        //return;
                    }
                }
            };

            return this;
        }
    });
});


