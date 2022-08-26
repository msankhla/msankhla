/**
 * FedEx Cross Border component
 *
 * @category    FedEx
 * @package     FedEx_CrossBorder
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define([
    'jquery',
    'Magento_Checkout/js/view/minicart',
    'Magento_Customer/js/customer-data'
], function ($, Component, customerData) {
    'use strict';

    return Component.extend({
        checkoutDomesticUrl : window.checkout.checkoutDomesticUrl,
        defaultCountry      : window.checkout.defaultCountry,
        isDomestic          : window.checkout.isDomestic,

        canShow: function () {
            var cartData = customerData.get('cart');
            return !this.isDomestic && cartData().checkout_widget;
        }
    });
});