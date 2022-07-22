/*
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Option
 * @copyright Copyright (c) 2020 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'underscore',
    'mage/utils/wrapper'
], function (_, wrapper) {
    'use strict';

    return function (cacheConstructor) {
        cacheConstructor.requiredFields.push('city');
        cacheConstructor.requiredFields = _.uniq(cacheConstructor.requiredFields);
        return cacheConstructor;
    };
});
