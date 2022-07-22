/*
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Option
 * @copyright Copyright (c) 2020 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */


var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                "ShipperHQ_Option/js/action/set-shipping-information": true
            },
            'Magento_Checkout/js/view/shipping': {
                'ShipperHQ_Option/js/view/shipping': true
            },
            'Magento_Checkout/js/model/cart/totals-processor/default': {
                'ShipperHQ_Option/js/model/cart/totals-processor-mixin': true
            },
            'Magento_Checkout/js/model/cart/cache': {
                'ShipperHQ_Option/js/model/cart/cache-mixin': true
            }
        }
    }
};
