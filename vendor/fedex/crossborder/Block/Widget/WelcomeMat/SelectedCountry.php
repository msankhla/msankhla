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

class SelectedCountry extends Template implements BlockInterface
{
    /**
     * @var AvailableCountries
     */
    protected $_availableCountries;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * SelectedCountry constructor.
     *
     * @param AvailableCountries $availableCountries
     * @param Helper $helper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        AvailableCountries $availableCountries,
        Helper $helper,
        Template\Context $context,
        array $data = []
    ) {
        $this->_availableCountries = $availableCountries;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        return $this->_availableCountries->getCountryName(
            $this->_helper->getSelectedCountry()
        );
    }
}