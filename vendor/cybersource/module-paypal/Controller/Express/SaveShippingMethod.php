<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace CyberSource\PayPal\Controller\Express;

class SaveShippingMethod extends \Magento\Paypal\Controller\Express\AbstractExpress\SaveShippingMethod
{
    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = \Magento\Paypal\Model\Config::class;

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = \CyberSource\PayPal\Model\Config::CODE;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = \CyberSource\PayPal\Model\Express\Checkout::class;
}
