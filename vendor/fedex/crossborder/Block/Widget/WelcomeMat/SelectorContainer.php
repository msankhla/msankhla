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
namespace FedEx\CrossBorder\Block\Widget\WelcomeMat;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use FedEx\CrossBorder\Helper\Data as Helper;
use FedEx\CrossBorder\Model\Config\Source\AvailableCountries;
use FedEx\CrossBorder\Model\Config\Source\AvailableCurrencies;
use FedEx\CrossBorder\Model\WelcomeMat;

class SelectorContainer extends Template implements BlockInterface
{
    const DEFAULT_SAVE_BUTTON_TITLE = 'UPDATE PREFERENCES';

    /**
     * @var AvailableCountries
     */
    protected $_availableCountries;

    /**
     * @var AvailableCurrencies
     */
    protected $_availableCurrencies;

    /**
     * @var string
     */
    protected $_infoBlock;

    /**
     * @var WelcomeMat
     */
    protected $_welcomeMat;

    /**
     * @var string
     */
    protected $_template = "widget/welcome_mat/selector_container.phtml";

    /**
     * SelectorContainer constructor.
     *
     * @param AvailableCountries $availableCountries
     * @param AvailableCurrencies $availableCurrencies
     * @param WelcomeMat $welcomeMat
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        AvailableCountries $availableCountries,
        AvailableCurrencies $availableCurrencies,
        WelcomeMat $welcomeMat,
        Template\Context $context,
        array $data = []
    ) {
        $this->_availableCountries = $availableCountries;
        $this->_availableCurrencies = $availableCurrencies;
        $this->_welcomeMat = $welcomeMat;
        parent::__construct($context, $data);
    }

    /**
     * Check if country title not empty
     *
     * @return bool
     */
    public function hasCountryTitle()
    {
        return !empty($this->_data['country_title']);
    }

    /**
     * Check if currency title not empty
     *
     * @return bool
     */
    public function hasCurrencyTitle()
    {
        return !empty($this->_data['currency_title']);
    }

    /**
     * Returns block html content by id
     *
     * @param mixed $blockId
     * @return string
     */
    public function getBlockContent($blockId)
    {
        $content = '';

        if (!empty($blockId)) {
            $content = $this->getLayout()->createBlock(
                'Magento\Cms\Block\Block'
            )->setBlockId(
                $blockId
            )->toHtml();
        }

        return $content;
    }

    /**
     * Returns options
     *
     * @return array
     */
    public function getCountryOptions()
    {
        return $this->_availableCountries->toOptionArray();
    }

    /**
     * Returns options
     *
     * @return array
     */
    public function getCurrencyOptions()
    {
        return $this->_availableCurrencies->toOptionArray();
    }

    /**
     * Returns helper
     *
     * @return Helper
     */
    public function getHelper()
    {
        return $this->_welcomeMat->getHelper();
    }

    /**
     * Returns info block
     *
     * @param string|null $countryCode
     * @return string
     */
    public function getInfoBlock($countryCode = null)
    {
        if (isset($countryCode)) {
            return $this->getBlockContent(
                $this->_welcomeMat->getInfoBlockIdByCountry($countryCode)
            );
        }

        if (!isset($this->_infoBlock)) {
            $this->_infoBlock = $this->getBlockContent(
                $this->_welcomeMat->getInfoBlockId()
            );
        }

        return $this->_infoBlock;
    }

    public function getSaveButtonTitle()
    {
        return $this->getData('save_button_title') ?: __(static::DEFAULT_SAVE_BUTTON_TITLE);
    }

    /**
     * Returns selected country
     *
     * @return string
     */
    public function getSelectedCountry()
    {
        return $this->getHelper()->getSelectedCountry();
    }

    /**
     * Returns selected currency
     *
     * @return string
     */
    public function getSelectedCurrency()
    {
        return $this->getHelper()->getSelectedCurrency();
    }

    /**
     * Returns post url
     *
     * @return string
     */
    public function getPostUrl()
    {
        return $this->getUrl('fdxcb/welcomeMat/save');
    }
}