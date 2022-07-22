<?php
/**
 *
 * Shipper HQ Calendar Module
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
 * @copyright Copyright (c) 2017 Zowta LLC (http://www.ShipperHQ.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Calendar\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form;
use ShipperHQ\Calendar\Model\CalendarConfigProvider;

/**
 * Shipper shipping model
 *
 * @category ShipperHQ
 * @package  ShipperHQ_Calendar
 */
class Calendarpicker extends Template
{
    protected $calendarConfig;

    /**
     * Calendarpicker constructor.
     *
     * @param Template\Context       $context
     * @param CalendarConfigProvider $calendarConfigProvider
     * @param array                  $data
     */
    public function __construct(Template\Context $context, CalendarConfigProvider $calendarConfigProvider, array $data = [])
    {
        parent::__construct($context, $data);
        $this->calendarConfig = $calendarConfigProvider;
    }

    public function getConfig()
    {
        $config = $this->calendarConfig->getCalendarConfig($this->getCarrier(), null, true);
        return isset($config['shipperhq_calendar']) ? $config['shipperhq_calendar'] : [];
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
            list($carrier, $method) = preg_split('/_/', $shipping_method);
        } catch (\Exception $e) {
            $carrier = null;
        }
        return $carrier;
    }

    public function getAdminRequestRatesUrl()
    {
        $url = $this->_urlBuilder->getUrl('shipperhq_calendar/requestRates');
        return $url;
    }
}
