/*
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Pickup
 * @copyright Copyright (c) 2020 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                "ShipperHQ_Pickup/js/action/set-shipping-information": true
            }
        }
    }
};
