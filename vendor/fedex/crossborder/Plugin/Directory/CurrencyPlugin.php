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
namespace FedEx\CrossBorder\Plugin\Directory;

use Magento\Directory\Model\Currency;
use FedEx\CrossBorder\Model\RoundedPrice;

class CurrencyPlugin
{
    /**
     * @var RoundedPrice
     */
    protected $_roundedPrice;

    /**
     * CurrencyPlugin constructor.
     *
     * @param RoundedPrice $roundedPrice
     */
    public function __construct(
        RoundedPrice $roundedPrice
    ) {
        $this->_roundedPrice = $roundedPrice;
    }

    /**
     * Checks if price can be rounded
     *
     * @return bool
     */
    public function canRound()
    {
        return (bool) (
            $this->_roundedPrice->isEnabled() &&
            (
                $this->_roundedPrice->isAllCountries() ||
                in_array(
                    $this->_roundedPrice->getHelper()->getSelectedCountry(),
                    $this->_roundedPrice->getCountries()
                )
            )
        );
    }

    /**
     * @param Currency $subject
     * @param callable $proceed
     * @param float $price
     * @param mixed|null $toCurrency
     * @return float
     */
    public function aroundConvert(
        Currency $subject,
        $proceed,
        $price,
        $toCurrency = null
    ) {
        $price = $proceed($price, $toCurrency);

        return ($this->canRound() ? ceil($price) : $price);
    }
}