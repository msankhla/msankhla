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
    'Magento_Tax/js/view/checkout/cart/totals/tax',
    'mage/translate'
], function (Component, $t) {
    'use strict';

    return Component.extend({
        isDomestic          : window.checkout.isDomestic,
        initialize: function () {
            this._super();
            if (!this.isDomestic) {
                this.title = $t('Duty & Tax');
            }
        }
    });
});
