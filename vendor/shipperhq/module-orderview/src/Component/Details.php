<?php
/**
 * Shipper HQ
 *
 * @category ShipperHQ
 * @package ShipperHQ_Orderview
 * @copyright Copyright (c) 2019 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Orderview\Component;

class Details extends \Magento\Ui\Component\AbstractComponent
{
    const NAME = 'shq_orderview_details';

    public function getComponentName()
    {
        return self::getName();
    }
}