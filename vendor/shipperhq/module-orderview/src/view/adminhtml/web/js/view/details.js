/*
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Orderview
 * @copyright Copyright (c) 2019 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

define([
    'uiElement',
    'ko',
    'jquery',
    window.shqConfig.orderview.bundleUrl,
], function(
    Element,
    ko,
    $,
    shqOrderView
){
    "use strict";

    var viewModelConstructor = Element.extend({
        defaults: {
            template: 'ShipperHQ_Orderview/details'
        },

        onTemplateRendered: function (element, viewmodel) {
            this.attachComponent({}, element);
        },

        attachComponent: function (config, element) {
            var orderNumber = $('h1.page-title').text().substr(1);
            shqOrderView.SHQRenderOrderView(element, orderNumber, window.shqConfig.orderview);
        }
    });

    return viewModelConstructor;
});
