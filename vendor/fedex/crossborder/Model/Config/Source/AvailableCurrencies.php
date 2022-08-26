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

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Locale\Bundle\CurrencyBundle;
use Magento\Framework\Locale\ResolverInterface as LocaleResolver;
use Magento\Store\Model\StoreManagerInterface;

class AvailableCurrencies implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $_currencyList;

    /**
     * @var LocaleResolver
     */
    protected $_localeResolver;

    /**
     * @var array
     */
    protected $_options;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * AvailableCurrencies constructor.
     *
     * @param LocaleResolver $localeResolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        LocaleResolver $localeResolver,
        StoreManagerInterface $storeManager
    ) {
        $this->_localeResolver = $localeResolver;
        $this->_storeManager = $storeManager;
    }

    /**
     * Returns base currency
     *
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * Returns currency list
     *
     * @return \ResourceBundle
     */
    public function getCurrencyList()
    {
        if (!isset($this->_currencyList)) {
            $this->_currencyList = (new CurrencyBundle())->get($this->_localeResolver->getLocale())['Currencies'];
        }

        return $this->_currencyList;
    }

    /**
     * Returns currency name
     *
     * @param string $currency
     * @return string
     */
    public function getCurrencyName($currency)
    {
        $name = $this->getCurrencyList()->get($currency);

        return ($name ? $name[1] : '');
    }

    /**
     * Checks if currency is available
     *
     * @param string $currency
     * @return bool
     */
    public function isAvailable($currency)
    {
        if (!isset($this->_options)) {
            $this->toOptionArray();
        }

        return isset($this->_options[$currency]);
    }

    /**
     * Return currencies options list
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!isset($this->_options)) {
            $this->_options = [];
            foreach ($this->_storeManager->getStore()->getAvailableCurrencyCodes() as $currency) {
                if ($name = $this->getCurrencyName($currency)) {
                    $this->_options[$currency] = [
                        'value' => $currency,
                        'label' => $name,
                    ];
                }
            }
        }

        return $this->_options;
    }
}