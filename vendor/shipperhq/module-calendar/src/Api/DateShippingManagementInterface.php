<?php
/**
 *
 * ShipperHQ
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Shipper HQ Shipping
 *
 * @category  ShipperHQ
 * @package   ShipperHQ_Calendar
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\Calendar\Api;

/**
 * Interface DateShippingManagementInterface
 * @api
 */
interface DateShippingManagementInterface
{
    /**
     * Return the config.
     *
     * @param string $cartId The shopping cart ID.
     * @return \Magento\GiftMessage\Api\Data\MessageInterface Gift message.
     */
//    public function getConfig($cartId);

    /**
     * Request shipping rates for selected date.
     *
     * @param string $cartId The cart ID.
     * @param  ????
     * @return bool
     */
    public function requestRates($cartId, \ShipperHQ\Calendar\Api\Data\DateShippingInformationInterface $dateShippingInformation);
}
