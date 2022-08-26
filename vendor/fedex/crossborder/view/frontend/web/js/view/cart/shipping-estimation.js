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
    'Magento_Checkout/js/view/cart/shipping-estimation'
], function ($ ,Component) {
    'use strict';

    return Component.extend({
        isDomestic          : window.checkout.isDomestic
    });
});