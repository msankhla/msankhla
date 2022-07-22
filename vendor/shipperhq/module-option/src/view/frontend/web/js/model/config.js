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
 * @copyright Copyright (c) 2016 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
define(
    ['ko', 'jquery'],
    function (ko, $) {
        'use strict';
        var loadConfigUrl = window.checkoutConfig.shipperhq_option.load_config_url;
        var carrier_code = window.checkoutConfig.shipperhq_option.carrier_code;
        var carrier_id = window.checkoutConfig.shipperhq_option.carrier_id;
        var carrier_group_id = window.checkoutConfig.shipperhq_option.carrier_group_id;
        var processing = false;
        var show_option = ko.observable(window.checkoutConfig.shipperhq_option.show_option);
        var show_destination_type = ko.observable(window.checkoutConfig.shipperhq_option.show_destination_type);
        var destination_type_values = ko.observable(window.checkoutConfig.shipperhq_option.destination_type_values);
        var destination_type_default_value = ko.observable(window.checkoutConfig.shipperhq_option.destination_type_default_value);
        var show_limited_delivery = ko.observable(window.checkoutConfig.shipperhq_option.show_limited_delivery);
        var limited_delivery_default_value = ko.observable(window.checkoutConfig.shipperhq_option.limited_delivery_default_value);
        var show_notify_required = ko.observable(window.checkoutConfig.shipperhq_option.show_notify_required);
        var notify_required_default_value = ko.observable(window.checkoutConfig.shipperhq_option.notify_required_default_value);
        var show_inside_delivery = ko.observable(window.checkoutConfig.shipperhq_option.show_inside_delivery);
        var inside_delivery_default_value = ko.observable(window.checkoutConfig.shipperhq_option.inside_delivery_default_value);
        var show_liftgate_required = ko.observable(window.checkoutConfig.shipperhq_option.show_liftgate_required);
        var liftgate_required_default_value = ko.observable(window.checkoutConfig.shipperhq_option.liftgate_required_default_value);
        var show_customer_carrier = ko.observable(window.checkoutConfig.shipperhq_option.show_customer_carrier);
        var show_customer_carrier_ph = ko.observable(window.checkoutConfig.shipperhq_option.show_customer_carrier_ph);
        var show_customer_carrier_account = ko.observable(window.checkoutConfig.shipperhq_option.show_customer_carrier_account);

        return {
            loadConfigUrl: loadConfigUrl,
            carrier_code: carrier_code,
            carrier_id: carrier_id,
            carrier_group_id: carrier_group_id,
            processing: processing,
            show_option: show_option,
            show_destination_type: show_destination_type,
            destination_type_values: destination_type_values,
            destination_type_default_value: destination_type_default_value,
            show_limited_delivery: show_limited_delivery,
            limited_delivery_default_value: limited_delivery_default_value,
            show_notify_required: show_notify_required,
            notify_required_default_value: notify_required_default_value,
            show_inside_delivery: show_inside_delivery,
            inside_delivery_default_value: inside_delivery_default_value,
            show_liftgate_required: show_liftgate_required,
            liftgate_required_default_value: liftgate_required_default_value,
            show_customer_carrier: show_customer_carrier,
            show_customer_carrier_ph: show_customer_carrier_ph,
            show_customer_carrier_account: show_customer_carrier_account,

            updateConfig: function (config) {
                this.carrier_code = config.carrier_code;
                this.carrier_id = config.carrier_id;
                this.show_option(config.show_option);
                this.show_destination_type(config.show_destination_type);
                this.destination_type_values(config.destination_type_values);
                this.destination_type_default_value(config.destination_type_default_value);
                this.show_limited_delivery(config.show_limited_delivery);
                this.limited_delivery_default_value(config.limited_delivery_default_value);
                this.show_notify_required(config.show_notify_required);
                this.notify_required_default_value(config.notify_required_default_value);
                this.show_inside_delivery(config.show_inside_delivery);
                this.inside_delivery_default_value(config.inside_delivery_default_value);
                this.show_liftgate_required(config.show_liftgate_required);
                this.liftgate_required_default_value(config.liftgate_required_default_value);
                this.show_customer_carrier(config.show_customer_carrier);
                this.show_customer_carrier_ph(config.show_customer_carrier_ph);
                this.show_customer_carrier_account(config.show_customer_carrier_account);
            },
            reloadConfig: function (callback, carrier) {
                var self = this;
                $.ajax({
                    type: 'GET',
                    url: self.loadConfigUrl,
                    data: {
                        'carrier': carrier
                    },
                    context: $('body')
                }).success(function (data) {
                    if (data.config) {
                        if (typeof data.config.shipperhq_option !== 'undefined') {
                            self.updateConfig(data.config.shipperhq_option);
                        }
                    }
                    callback();
                });
            },
            getAddressTypes: function () {
                var newaddresstypes = this.destination_type_values();
                if ((typeof(newaddresstypes) == "undefined") || newaddresstypes.length == 0) {
                    return [];
                }
                var addresstypes = [];
                for (var id in newaddresstypes) {
                    var addType = {
                        key: id,
                        value: newaddresstypes[id]
                    };
                    addresstypes.push(addType);
                }
                return addresstypes;
            }

        };
    }
);