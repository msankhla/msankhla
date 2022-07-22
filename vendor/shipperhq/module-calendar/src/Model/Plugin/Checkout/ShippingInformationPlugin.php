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
 * @package ShipperHQ_Calendar
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\Calendar\Model\Plugin\Checkout;

use Magento\Framework\Session\SessionManagerInterface;

class ShippingInformationPlugin
{
    /**
     * @var \ShipperHQ\Shipper\Helper\Data
     */
    protected $shipperDataHelper;
    /**
     * @var \ShipperHQ\Shipper\Helper\CarrierGroup
     */
    protected $carrierGroupHelper;
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var SessionManagerInterface
     */
    private $checkoutSession;
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \ShipperHQ\Shipper\Model\Quote\AddressDetailFactory
     */
    private $addressDetailFactory;

    public function __construct(
        \ShipperHQ\Shipper\Helper\Data $shipperDataHelper,
        \ShipperHQ\Shipper\Helper\CarrierGroup $carrierGroupHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        SessionManagerInterface $checkoutSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \ShipperHQ\Shipper\Model\Quote\AddressDetailFactory $addressDetailFactory
    ) {
        $this->shipperDataHelper = $shipperDataHelper;
        $this->carrierGroupHelper = $carrierGroupHelper;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->addressRepository = $addressRepository;
        $this->addressDetailFactory = $addressDetailFactory;
    }

    /**
     * Set additional information for shipping address
     *
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     *
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface $paymentDetails
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {

        $requestData = $this->checkoutSession->getShipperhqData();
        $selectedCarrierCode = $addressInformation->getShippingCarrierCode();
        $selectedCarrierMethod = $addressInformation->getShippingMethodCode();

        if ($requestData && isset($requestData['checkout_selections'])) {
            $savedData = $requestData['checkout_selections'];
            $savedCarrierCode = $savedData->getCarrierCode();
            if ($savedCarrierCode != $selectedCarrierCode) {
                //wipe the checkout selections
                $requestData['checkout_selections'] = [];
            }
        }
    }
}
