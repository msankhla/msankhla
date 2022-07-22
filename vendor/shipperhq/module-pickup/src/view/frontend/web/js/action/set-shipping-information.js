/*
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Pickup
 * @copyright Copyright (c) 2020 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'ShipperHQ_Calendar/js/model/config'
], function ($, wrapper, quote, calendarConfig) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }
            var locationId = $('input[name="shipperhq_location"]').val();

            shippingAddress['extension_attributes']['delivery_date'] = calendarConfig.show_calendar() ? $('#shipperhq_calendar').val() : "";
            shippingAddress['extension_attributes']['time_slot'] = calendarConfig.show_calendar() ? $('#shipperhq_timeslots').val(): "";
            shippingAddress['extension_attributes']['location_id'] = locationId;
            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            var outcome = originalAction();

            if(locationId) {
                var populateAddressUrl = window.checkoutConfig.shipperhq_pickup.populate_location_address_url;
                var shipping_carrier_code = quote.shippingMethod().carrier_code;
                $.ajax({
                    type: 'GET',
                    url: populateAddressUrl,
                    data: {
                        'carrier': shipping_carrier_code,
                        'location_id': locationId
                    },
                    context: $('body')
                }).success(function (data) {

                    if (data.address) {
                        //SHQ16-2219 - removed this as setting shipping address here meant it was not refreshed if moving back to shipping method page
                        // shippingAddress.city = data.address.city;
                        // shippingAddress.postcode = data.address.zipcode;
                        // shippingAddress.region = data.address.region;
                        // shippingAddress.countryId = data.address.country;
                        // shippingAddress.company = data.address.company;
                        // var streetArray = [data.address.street1, data.address.street2];
                        // shippingAddress.street = streetArray;
                        shippingAddress['extension_attributes']['location_address'] = data.address.location_address;
                        quote.shippingAddress(shippingAddress);

                    }
                });

            } else {
                shippingAddress['extension_attributes']['location_address'] = '';
            }
            quote.shippingAddress(shippingAddress);

            return outcome;

        });

    };
});
