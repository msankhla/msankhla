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
use FedEx\CrossBorder\Model\ResourceModel\AvailableCountries\Collection;

class AvailableCountries implements OptionSourceInterface
{
    /**
     * @var AvailableCurrencies
     */
    protected $_availableCurrencies;
    /**
     * @var Collection
     */
    protected $_collection;

    /**
     * @var array
     */
    protected $_options;

    /**
     * AvailableCountries constructor.
     *
     * @param Collection $collection
     */
    public function __construct(
        AvailableCurrencies $availableCurrencies,
        Collection $collection
    ) {
        $this->_availableCurrencies = $availableCurrencies;
        $this->_collection = $collection->setOrder('name', Collection::SORT_ORDER_ASC);
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Returns country currency
     *
     * @param string $country
     * @return string
     */
    public function getCurrency($country)
    {
        return (!empty($country) && $this->isAvailable($country) ? $this->_options[$country]['currency'] : '');
    }

    /**
     * Returns country name
     *
     * @param string $country
     * @return string
     */
    public function getCountryName($country)
    {
        return ($this->isAvailable($country) ? $this->_options[$country]['label'] : '');
    }

    /**
     * Checks if country is available
     *
     * @param string $country
     * @return bool
     */
    public function isAvailable($country) {
        if (!isset($this->_options)) {
            $this->toOptionArray();
        }

        return isset($this->_options[$country]);
    }

    /**
     * Return countries options list
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!isset($this->_options)) {
            $this->_options = [];
            foreach ($this->_collection as $item) {
                $this->_options[$item->getCode()] = [
                    'value'     => $item->getCode(),
                    'label'     => $item->getName(),
                    'currency'  => ($this->_availableCurrencies->isAvailable($item->getCurrency()) ?
                        $item->getCurrency() :
                        $this->_availableCurrencies->getBaseCurrency()
                    ),
                ];
            }
        }

        return $this->_options;
    }
}