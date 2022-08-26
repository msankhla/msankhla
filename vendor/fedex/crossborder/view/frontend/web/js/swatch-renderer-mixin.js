/**
 * FedEx Cross Border component
 *
 * @category    FedEx
 * @package     FedEx_CrossBorder
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define(['jquery'], function ($) {
    'use strict';

    return function (SwatchRenderer) {
        $.widget('mage.SwatchRenderer', $['mage']['SwatchRenderer'], {
            options: {
                selectorAddToCart: '.box-tocart',
                selectorWarning: '[data-fdxcb-name="warning"]'
            },

            _checkProductAvailability: function () {
                var isAailable = this.options.jsonConfig.productsAvailability && this.options.jsonConfig.productsAvailability[this.getProduct()],
                    warning = $(this.options.selectorWarning);

                if (this.options.jsonConfig.isInternational) {
                    if (isAailable) {
                        this._getButton().removeClass('hide');
                        warning.addClass('hide');
                    } else {
                        this._getButton().addClass('hide');
                        if (this.getProduct()) {
                            warning.removeClass('hide');
                        } else {
                            warning.addClass('hide');
                        }
                    }
                }
            },

            _getButton: function () {
                return $('[data-fdxcb-name="addtocart-container-' + this.options.jsonConfig.productId + '"]');
            },

            _init: function () {
                this._super();
                this._checkProductAvailability();
            },

            _OnClick: function ($this, $widget, eventName) {
                this._super($this, $widget, eventName);
                this._checkProductAvailability();
            }
        });
        return $['mage']['SwatchRenderer'];
    };
});