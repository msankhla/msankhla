<?php
/**
 * FedEx Core component
 *
 * @category    FedEx
 * @package     FedEx_Core
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\Core\Helper;

use Magento\Store\Model\ScopeInterface;

abstract class AbstractHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    protected $templateMap      = [
    ];

    /**
     * Returns config value
     *
     * @param $path
     * @param null $defValue
     * @param string $scopeType
     * @param null $scopeCode
     * @return mixed|null
     */
    public function getConfig($path, $defValue = null, $scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        $result = $this->scopeConfig->getValue($path, $scopeType, $scopeCode);

        return (isset($defValue) && empty($result) ? $defValue : $result);
    }

    /**
     * Replace template
     *
     * @param string $name
     * @return string
     */
    public function replaceTemplate($name)
    {
        if ((!method_exists($this, 'isEnabled') || $this->isEnabled()) && isset($this->templateMap[$name])) {
            return $this->templateMap[$name];
        }

        return $name;
    }
}