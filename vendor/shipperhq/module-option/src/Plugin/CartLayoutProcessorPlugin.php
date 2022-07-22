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

namespace ShipperHQ\Option\Plugin;

use \Magento\Checkout\Block\Checkout\AttributeMerger;
use \Magento\Checkout\Block\Cart\LayoutProcessor;
use \ShipperHQ\Shipper\Helper\Data as SHQDataHelper;

class CartLayoutProcessorPlugin
{
    /**
     * @var AttributeMerger
     */
    protected $merger;

    /**
     * @var SHQDataHelper
     */
    protected $dataHelper;

    /**
     * @param AttributeMerger $merger
     * @param SHQDataHelper $dataHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        AttributeMerger $merger,
        SHQDataHelper $dataHelper
    ) {
        $this->merger = $merger;
        $this->dataHelper = $dataHelper;
    }

    public function isCityActive() {
        $config = $this->dataHelper->getConfigFlag('carriers/shipper/CARRIER_CITY_REQUIRED_DEFAULT');
        return $config;
    }

    public function afterProcess(LayoutProcessor $layoutProcessor, $jsLayout)
    {
        if ($this->isCityActive()) {
            $elements = [
                'city' => [
                    'visible' => $this->isCityActive(),
                    'formElement' => 'input',
                    'label' => __('City'),
                    'value' => null,
                    'sortOrder' => '113'
                ]
            ];

            if (isset($jsLayout['components']['block-summary']['children']['block-shipping']['children']
                ['address-fieldsets']['children'])
            ) {
                $fieldSetPointer = &$jsLayout['components']['block-summary']['children']['block-shipping']
                ['children']['address-fieldsets']['children'];
                $fieldSetPointer = $this->merger->merge($elements, 'checkoutProvider', 'shippingAddress', $fieldSetPointer);
                $fieldSetPointer['city']['sortOrder'] = '113'; //merge is taking default value over updated value so enforcing here
            }
        }
        return $jsLayout;
    }
}