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
namespace FedEx\CrossBorder\Block\Adminhtml\Config\Form\WelcomeMat;

use FedEx\Core\Block\Adminhtml\Config\Form\AbstractMultipleFields;
use FedEx\CrossBorder\Model\Config\Source\AvailableCountries as CountryOptions;
use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Config\Source\Block as BlockOptions;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;

class CountryCmsBlock extends AbstractMultipleFields
{
    /**
     * @var array
     */
    protected $_countryOptions;

    /**
     * @var array
     */
    protected $_blockOptions;

    /**
     * CmsBlocks constructor.
     * @param BlockOptions $blockOptions
     * @param CountryOptions $countryOptions
     * @param Context $context
     * @param ElementFactory $elementFactory
     * @param array $data
     */
    public function __construct(
        BlockOptions $blockOptions,
        CountryOptions $countryOptions,
        Context $context,
        ElementFactory $elementFactory,
        array $data = []
    ) {
        $this->_blockOptions = array_merge(
            [[
                'value' => '',
                'label' => __('--Please select the block--'),
            ]],
            $blockOptions->toOptionArray()
        );
        $this->_countryOptions = $countryOptions;
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
            $this->_countryOptions->toOptionArray(),
            [
                'label' => __('Country/Territory'),
                'class' => 'validate-select'
            ]
        )->addColumnInfo(
            'cms',
            'select',
            $this->_blockOptions,
            [
                'label' => __('Cms Block'),
                'class' => 'validate-select'
            ]
        )->addColumnInfo(
            'info',
            'select',
            $this->_blockOptions,
            [
                'label' => __('Info Block'),
            ]
        );

        parent::_prepareToRender();
    }
}
