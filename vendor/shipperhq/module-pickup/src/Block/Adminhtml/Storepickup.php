<?php
/**
 *
 * Shipper HQ Pickup Module
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
 * @package   ShipperHQ_Pickup
 * @copyright Copyright (c) 2017 Zowta LLC (http://www.ShipperHQ.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Pickup\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form;
use ShipperHQ\Pickup\Model\PickupConfigProvider;

/**
 * Shipper shipping model
 *
 * @category ShipperHQ
 * @package  ShipperHQ_Pickup
 */
class Storepickup extends Template
{
    protected $pickupConfig;

    /**
     * Storepickup constructor.
     *
     * @param Template\Context     $context
     * @param PickupConfigProvider $pickupConfigProvider
     * @param array                $data
     */
    public function __construct(Template\Context $context, PickupConfigProvider $pickupConfigProvider, array $data = [])
    {
        parent::__construct($context, $data);
        $this->pickupConfig = $pickupConfigProvider;
    }

    public function getConfig()
    {
        $config = $this->pickupConfig->getPickupConfig($this->getCarrier());
        return $config['shipperhq_pickup'];
    }

    /**
     * Get grandparent block
     *
     * @return bool|Form
     */
    public function getFormBlock()
    {
        return $this->getParentBlock()->getParentBlock();
    }

    public function getCarrier()
    {
        $shipping_method = $this->getFormBlock()->getShippingMethod();
        try {
            list($carrier, $method) = preg_split('/_/', (string) $shipping_method);
        } catch (\Exception $e) {
            $carrier = null;
        }
        return $carrier;
    }

    public function getAdminRequestRatesUrl()
    {
        $url = $this->_urlBuilder->getUrl('shipperhq_pickup/requestRates');
        return $url;
    }
}
