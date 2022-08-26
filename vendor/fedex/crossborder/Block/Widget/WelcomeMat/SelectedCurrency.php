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
use FedEx\CrossBorder\Model\Config\Source\AvailableCurrencies;

class SelectedCurrency extends Template implements BlockInterface
{
    /**
     * @var AvailableCurrencies
     */
    protected $_availableCurrencies;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * SelectedCountry constructor.
     *
     * @param AvailableCurrencies $availableCurrencies
     * @param Helper $helper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        AvailableCurrencies $availableCurrencies,
        Helper $helper,
        Template\Context $context,
        array $data = []
    ) {
        $this->_availableCurrencies = $availableCurrencies;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_availableCurrencies->getCurrencyName(
            $this->_helper->getSelectedCurrency()
        );
    }
}