<?php
/**
 *
 * ShipperHQ Shipping Module
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
 * @category ShipperHQ
 * @package ShipperHQ_Shipping_Carrier
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\Calendar\Model\Plugin\Quote;

use Magento\Framework\Session\SessionManagerInterface;

class ShippingMethodManagementPlugin
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;
    /**
     * @var SessionManagerInterface
     */
    private $checkoutSession;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        SessionManagerInterface $checkoutSession,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->shipperLogger = $shipperLogger;
    }

    /**
     * MNB-176 If rates are reloaded from the shipping checkout page we need to flush calendar/option settings
     *
     * Not changing the method inputs so we can ignore the params
     * @return null
     */
    public function beforeEstimateByExtendedAddress(
        \Magento\Quote\Model\ShippingMethodManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address
    ) {
        return $this->wipeSHQDateFromCheckoutSession();
    }

    /**
     * MNB-176 If rates are reloaded from the shipping checkout page we need to flush calendar/option settings
     *
     * @param void Not changing the method inputs so we can ignore the params
     * @return null
     */
    public function beforeEstimateByAddress()
    {
        return $this->wipeSHQDateFromCheckoutSession();
    }

    /**
     * MNB-176 If rates are reloaded from the shipping checkout page we need to flush calendar/option settings
     *
     * @param void Not changing the method inputs so that we can ignore the params
     * @return null
     */
    public function beforeEstimateByAddressId()
    {
        return $this->wipeSHQDateFromCheckoutSession();
    }

    /**
     * @return null
     */
    private function wipeSHQDateFromCheckoutSession()
    {
        $checkoutData = $this->checkoutSession->getShipperhqData();

        // MNB-1024 We only want to wipe out the selected options E.g delivery date
        if (!empty($checkoutData) && array_key_exists('checkout_selections', $checkoutData)) {
            unset($checkoutData['checkout_selections']);
        }

        $this->checkoutSession->setShipperhqData($checkoutData);
        return null; // Do not modify arguments
    }
}
