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
namespace FedEx\CrossBorder\Block\Adminhtml\Config\Form\Currency;

use FedEx\Core\Block\Adminhtml\Config\Form\AbstractMultipleFields;
use Magento\Config\Model\Config\Source\Locale\Country as CountryOptions;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Model\Config\Source\Locale\Currency as CurrencyOptions;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;

class Country extends AbstractMultipleFields
{
    /**
     * @var array
     */
    protected $_countryOptions;

    /**
     * @var array
     */
    protected $_currencyOptions;

    /**
     * Country constructor.
     *
     * @param CountryOptions $countryOptions
     * @param CurrencyOptions $currencyOptions
     * @param Context $context
     * @param ElementFactory $elementFactory
     * @param array $data
     */
    public function __construct(
        CountryOptions $countryOptions,
        CurrencyOptions $currencyOptions,
        Context $context,
        ElementFactory $elementFactory,
        array $data = []
    ) {
        $this->_countryOptions = $countryOptions->toOptionArray();
        $this->_currencyOptions = $currencyOptions->toOptionArray();
        parent::__construct($context, $elementFactory, $data);
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumnInfo(
            'country',
            'select',
            $this->_countryOptions,
            [
                'label' => __('Country'),
                'class' => 'validate-select'
            ]
        )->addColumnInfo(
            'currency',
            'select',
            $this->_currencyOptions,
            [
                'label' => __('Currency'),
                'class' => 'validate-select'
            ]
        );

        parent::_prepareToRender();
    }
}
