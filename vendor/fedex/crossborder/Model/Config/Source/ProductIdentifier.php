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

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Option\ArrayInterface;

class ProductIdentifier implements ArrayInterface
{
    /**
     * @var AbstractCollection
     */
    protected $_collection;

    /**
     * ProductIdentifier constructor.
     *
     * @param Attribute $attribute
     */
    public function __construct(
        Attribute $attribute
    ) {
        $this->_collection = $attribute->getCollection(
        )->addFieldToFilter(
            'entity_type_id',
            4
        )->addFieldToFilter(
            'is_required',
            1
        )->addFieldToFilter(
            'is_unique',
            1
        );
    }

    /**
     * Returns options
     *
     * @return array
     */
    public function getOptions()
    {
        $result = [
            'entity_id' => 'ID',
        ];

        foreach ($this->_collection as $attribute) {
            $result[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        return $result;
    }

    /**
     * Returns dimension units list
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $result;
    }
}
