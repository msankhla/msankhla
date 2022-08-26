<?php
/**
 * FedEx Cross Border component
 *
 * @category    FedEx
 * @package     FedEx_CrossBorder
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\CrossBorder\Model\Config\Source;

use FedEx\CrossBorder\Model\Carrier\Shipping;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;

class DomesticShippingMethod implements ArrayInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Config
     */
    protected $_shippingConfig;

    /**
     * DomesticShippingMethod constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $shippingConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $shippingConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_shippingConfig = $shippingConfig;
    }

    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     */
    public function toOptionArray($isActiveOnlyFlag = false)
    {
        $methods = [[
            'value' => '',
            'label' => '--Please select the method--',
        ]];

        $carriers = $this->_shippingConfig->getAllCarriers();
        foreach ($carriers as $carrierCode => $carrierModel) {
            if ((!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) ||
                ($carrierCode == Shipping::CODE)) {
                continue;
            }

            $carrierMethods = $carrierModel->getAllowedMethods();
            if (!$carrierMethods) {
                continue;
            }

            $carrierTitle = $this->_scopeConfig->getValue(
                'carriers/' . $carrierCode . '/title',
                ScopeInterface::SCOPE_STORE
            );
            $methods[$carrierCode] = [
                'value' => [],
                'label' => $carrierTitle
            ];
            foreach ($carrierMethods as $methodCode => $methodTitle) {
                $title = '';
                if (!is_array($methodTitle)) {
                    $title .= $methodTitle;
                    if (!empty($title)) {
                        $methods[$carrierCode]['value'][] = [
                            'value' => $carrierCode . '_' . $methodCode,
                            'label' => '[' . $carrierCode . '] ' . $title,
                        ];
                    }
                }
            }
        }

        return $methods;
    }
}
