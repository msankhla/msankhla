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
    'mage/utils/wrapper'
], function ($, wrapper) {
    "use strict";

    return function (target) {
        return wrapper.wrap(target, function (originalFunction, config, element) {
            var list = $(element).find('[data-selector^="super_group"]'),
                selectorAddToCart = '#product-addtocart-button',
                isAvailable;

            function validate() {
                isAvailable = true;
                list.each(function () {
                    if (config.isInternational &&
                        $(this).val() > 0 &&
                        !$(this).data('fdxcbAvailable')
                    ) {
                        isAvailable = false;
                    }
                });

                if (isAvailable) {
                    $(selectorAddToCart).show();
                } else {
                    $(selectorAddToCart).hide();
                }
            }

            $('[data-selector^="super_group"]').change(function () {
                validate();
            });

            validate();
            originalFunction(config, element);
        });
    };
});