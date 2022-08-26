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
    'mage/template'
], function ($, mageTemplate) {
    "use strict";
    
    return function (productSummary) {
        $.widget('mage.productSummary', productSummary, {
            options: {
                selectorAddToCart: '#bundleSummary .box-tocart',
                isAvailable: true
            },

            _renderSummaryBox: function (event, data) {
                this.options.isAvailable = true;
                this._super(event, data);
                if (this.options.isAvailable) {
                    $(this.options.selectorAddToCart).show();
                } else {
                    $(this.options.selectorAddToCart).hide();
                }
            },

            _renderOptionRow: function (key, optionIndex) {
                var template,
                    item = this.cache.currentElement.options[this.cache.currentKey].selections[optionIndex],
                    isAvailable = !this.options.isInternational || (this.options.productsAvailability && this.options.productsAvailability[item.optionId]);

                if (!isAvailable) {
                    this.options.isAvailable = false;
                }

                template = this.element
                    .closest(this.options.summaryContainer)
                    .find(this.options.templates.optionBlock)
                    .html();

                template = mageTemplate($.trim(template), {
                    data: {
                        _quantity_: item.qty,
                        _label_: item.name,
                        _available_: isAvailable
                    }
                });

                this.cache.summaryContainer
                    .find(this.options.optionSelector)
                    .append(template);
            }
        });

        return $.mage.productSummary;
    }
});