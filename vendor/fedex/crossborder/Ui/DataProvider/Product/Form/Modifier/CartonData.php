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
namespace FedEx\CrossBorder\Ui\DataProvider\Product\Form\Modifier;

use FedEx\CrossBorder\Model\Config\Source\AvailableCurrencies;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Field;

class CartonData extends AbstractModifier
{
    const FIELDSET_NAME         = 'fedex-carton';
    const FIELD_NAME            = 'fdx_carton';
    const FIELD_WIDTH           = 'width';
    const FIELD_HEIGHT          = 'height';
    const FIELD_LENGTH          = 'length';
    const FIELD_WEIGHT          = 'weight';
    const FIELD_SORT_ORDER_NAME = 'sort_order';

    const LABEL_WIDTH           = 'Width';
    const LABEL_HEIGHT          = 'Height';
    const LABEL_LENGTH          = 'Length';
    const LABEL_WEIGHT          = 'Weight';

    /**
     * @var AvailableCurrencies
     */
    protected $_availableCurrencies;

    /**
     * @var LocatorInterface
     */
    protected $_locator;

    /**
     * @var int
     */
    protected $_sortOrder       = 110;

    /**
     * AdditionalData constructor.
     *
     * @param AvailableCurrencies $availableCurrencies
     * @param LocatorInterface $locator
     */
    public function __construct(
        AvailableCurrencies $availableCurrencies,
        LocatorInterface $locator
    ) {
        $this->_availableCurrencies = $availableCurrencies;
        $this->_locator = $locator;
    }

    /**
     * @param array $data
     * @return array
     * @since 100.1.0
     */
    public function modifyData(array $data)
    {
        $product = $this->_locator->getProduct();
        $productId = $product->getId();
        $data[$productId]['product'][static::FIELD_NAME] = $product->getData(static::FIELD_NAME);

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     * @since 100.1.0
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                static::FIELDSET_NAME => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label'         => __('Item Dimensions & Weights'),
                                'componentType' => Fieldset::NAME,
                                'dataScope'     => 'data.product',
                                'collapsible'   => true,
                                'sortOrder'     => $this->_sortOrder,
                            ],
                        ],
                    ],
                    'children' => [
                        static::FIELD_NAME  => $this->_getCartonConfig()
                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * Returns carton config
     *
     * @return array
     */
    protected function _getCartonConfig() {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel'            => __('Add New Entry'),
                        'componentType'             => DynamicRows::NAME,
                        'component'                 => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses'         => 'admin__field-wide',
                        'renderDefaultRecord'       => false,
                        'sortOrder'                 => 1,
                        'dataScope'                 => '',
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType'     => Container::NAME,
                                'component'         => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider'  => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate'        => true,
                                'is_collection'     => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::FIELD_WIDTH      => $this->_getInputConfig(static::FIELD_WIDTH, static::LABEL_WIDTH, 1),
                        static::FIELD_HEIGHT     => $this->_getInputConfig(static::FIELD_HEIGHT, static::LABEL_HEIGHT, 2),
                        static::FIELD_LENGTH     => $this->_getInputConfig(static::FIELD_LENGTH, static::LABEL_LENGTH, 3),
                        static::FIELD_WEIGHT     => $this->_getInputConfig(static::FIELD_WEIGHT, static::LABEL_WEIGHT, 4),
                        $this->_getDeleteConfig(),
                    ]
                ]
            ]
        ];
    }


    /**
     * Returns input field config
     *
     * @param string $dataScope
     * @param string $label
     * @return array
     */
    protected function _getInputConfig($dataScope, $label, $sortOrder = 0)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement'   => Input::NAME,
                        'componentType' => Field::NAME,
                        'dataType'      => Text::NAME,
                        'dataScope'     => $dataScope,
                        'label'         => __($label),
                        'sortOrder'     => $sortOrder,
                        'fit'           => false,
                        'validation'    => [
                            'required-entry' => true,
                            'validate-greater-than-zero' => true,
                            'validate-digits' => false,
                            'validate-number' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns delete button config
     *
     * @return array
     */
    protected function _getDeleteConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => ActionDelete::NAME,
                        'fit'           => true,
                        'sortOrder'     => 100
                    ],
                ],
            ],
        ];
    }
}