/*
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Option
 * @copyright Copyright (c) 2020 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

define(
    [
        'jquery',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Magento_Checkout/js/model/shipping-rate-service'
    ],function (
        $,
        _,
        Component,
        ko,
        customer,
        addressList,
        addressConverter,
        quote,
        createShippingAddress,
        selectShippingAddress,
        shippingRatesValidator,
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        setShippingInformationAction,
        stepNavigator,
        modal,
        checkoutDataResolver,
        checkoutData,
        registry,
        $t) {
        'use strict';

        var mixin = {
            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {
                var customCarrier = $('#shipperhq_customer_carrier');
                if (customCarrier.length) {
                    var valueEntered = customCarrier.val();
                    var koData =  ko.dataFor($('#shq_customer_carrier')[0]);
                    var showCarrier = koData.shouldShowCustomerCarrier();
                    var showCarrierPh = koData.shouldShowCustomerCarrierPh();
                    var showCarrierAccount = koData.shouldShowCustomerCarrierAccount();
                    if (showCarrier && valueEntered == '') {
                        this.errorValidationMessage('Please enter custom carrier details.');
                       return false;
                   }
                   /* MNB-399 Make field not required

                   if (showCarrierPh && $('#shipperhq_customer_carrier_ph').val() == '') {
                       this.errorValidationMessage('Please enter custom carrier phone details.');
                       return false;
                   }
                   */
                   if (showCarrierAccount && $('#shipperhq_customer_carrier_account').val() == '') {
                        this.errorValidationMessage('Please enter custom carrier account details.');
                        return false;
                   }
                }
                var result = this._super();
                return result;
            }
        };

        return function (target) {
            return target.extend(mixin);
        };
    });
