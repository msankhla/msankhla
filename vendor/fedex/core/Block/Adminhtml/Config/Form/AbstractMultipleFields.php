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
namespace FedEx\Core\Block\Adminhtml\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;

class AbstractMultipleFields extends AbstractFieldArray
{
    /**
     * @var ElementFactory
     */
    protected $_elementFactory;

    /**
     * @var array
     */
    protected $_columnInfo = [];

    /**
     * AbstractMultipleFields constructor.
     *
     * @param Context $context
     * @param ElementFactory $elementFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ElementFactory $elementFactory,
        array $data = []
    ) {
        $this->_elementFactory  = $elementFactory;
        $this->_addAfter        = false;

        parent::__construct($context, $data);
    }

    /**
     * Add column element information
     *
     * @param string $name
     * @param string $type
     * @param array $options
     * @param array $params
     * @return $this
     */
    public function addColumnInfo($name, $type, $options, $params)
    {
        $this->_columnInfo[$name] = [
            'type'      => $type,
            'options'   => $options,
            'params'    => $params
        ];

        return $this;
    }

    /**
     * Returns column elements information
     *
     * @return array
     */
    public function getColumnInfo()
    {
        return $this->_columnInfo;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        foreach ($this->getColumnInfo() as $name => $info) {
            $this->addColumn($name, $info['params']);
        }
    }

    /**
     * @param string $columnName
     *
     * @return mixed|string
     */
    public function renderCellTemplate($columnName)
    {
        if (isset($this->_columnInfo[$columnName])) {
            $element = $this->_elementFactory->create($this->_columnInfo[$columnName]['type']);
            $element->setForm(
                $this->getForm()
            )->setName(
                $this->_getCellInputElementName($columnName)
            )->setHtmlId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setValues(
                (is_array($this->_columnInfo[$columnName]['options']) ? $this->_columnInfo[$columnName]['options'] : [])
            );

            return str_replace("\n", '', $element->getElementHtml());
        }

        return parent::renderCellTemplate($columnName);
    }
}
