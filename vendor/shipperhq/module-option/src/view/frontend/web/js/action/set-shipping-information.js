/*
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Option
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
    'ShipperHQ_Option/js/model/config'
], function ($, wrapper, quote, optionConfig) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            optionConfig.processing = true; // MNB-2354 prevent reloading config of accessorial options on checkout when clicking proceed
            var shippingAddress = quote.shippingAddress();
            var shippingMethod = quote.shippingMethod();
            var carrierCode = shippingMethod.carrier_code;
            var isCustomerAccount = carrierCode.indexOf('account') !== -1 ;

            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }
            var returnValues = {
                'destination_type' : '',
                'inside_delivery' : '',
                'liftgate_required' : '',
                'limited_delivery' : '',
                'notify_required': '',
                'customer_carrier': '',
                'customer_carrier_ph': '',
                'customer_carrier_account': ''
            };

            var customerAccountFields = [
                'customer_carrier',
                'customer_carrier_ph',
                'customer_carrier_account'
            ];
            //TODO enforce required fields
            var validated = true;

            $("[name=shipperhq-option]").each(function () {
                var code = this.id.replace('shipperhq_', '');
                var value = this.value;
                var thisIsACustomerAccountField = customerAccountFields.indexOf(code) > -1;
                if (this.type == 'checkbox') {
                    value = (this.checked ? "1" : "0");
                }
                if(thisIsACustomerAccountField) {
                    if(isCustomerAccount) {
                        if(value === '') {
                            //customer account field and customer account carrier is selected
                            // but field is blank so don't proceed as it's required
                            //This throws an error - need to improve validation to alert customer before we enable
                          // validated = false;
                        }
                        returnValues[code] = value;
                    }
                }
                else {
                    if (value !== '') {
                        returnValues[code] = value;
                    }
                }
            });
            if(validated) {
                shippingAddress['extension_attributes']['shipperhq_option_values'] = returnValues;
                // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
                return originalAction();
            }
        });
    };
});
