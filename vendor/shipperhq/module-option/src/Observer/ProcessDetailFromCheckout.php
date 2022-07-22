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
 * @package ShipperHQ_Option
 * @copyright Copyright (c) 2017 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Option\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * ShipperHQ Shipper module observer
 */
class ProcessDetailFromCheckout implements ObserverInterface
{
    /**
     * @var \ShipperHQ\Common\Model\Option
     */
    protected $optionImpl;
    /**
     * @var \ShipperHQ\Shipper\Helper\LogAssist
     */
    private $shipperLogger;

    /**
     * @param \ShipperHQ\Common\Model\Option $optionImpl
     * @param \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
     */
    public function __construct(
        \ShipperHQ\Common\Model\Option $optionImpl,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
    ) {

        $this->optionImpl = $optionImpl;
        $this->shipperLogger = $shipperLogger;
    }

    /**
     * Process option fields on checkout
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {

        $destinationType = null;
        $addressExtensionAttributes = $observer->getEvent()->getAddressExtnAttributes();
        $additionalDetail = $observer->getAdditionalDetail();
        $address = $observer->getAddress();
        $checkoutSelection = [];
        if ($addressExtensionAttributes) {
            $optionValues = $addressExtensionAttributes->getShipperhqOptionValues();
            if (is_array($optionValues) && !empty($optionValues)) {
                foreach ($optionValues as $code => $value) {
                    $value = $value == 'on' ? '1' : $value;
                    $additionalDetail->setData($code, $value);
                    if ($code == 'destination_type') {
                        if ($value != '') {
                            $address->setDestinationType($destinationType);
                        } elseif ($address->getDestinationType() != '') {
                            $additionalDetail->setData($code, $address->getDestinationType());
                            $value = $address->getDestinationType(); //SHQ16-2178 use value persisted to address
                        }
                    }
                    $checkoutSelection[$code] = $value;
                }
            }
            if (count($checkoutSelection) > 0) {
                //SHQ18-277 modify method of persisting to ensure other module's selections are not wiped
                //persisting the data selected here so payment and order review shipping requests are correct
                $this->optionImpl->saveOptionSelectOnCheckoutProceed($checkoutSelection, '', '', '');
            }
        }

        return $this;
    }
}
