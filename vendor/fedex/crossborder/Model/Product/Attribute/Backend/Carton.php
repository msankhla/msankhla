<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FedEx\CrossBorder\Model\Product\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Carton extends AbstractBackend
{
    /**
     * Returns volume
     *
     * @param array $data
     * @return float
     */
    protected function _getVolume($data)
    {
        return (isset($data['width']) ? $data['width'] : 0) *
            (isset($data['height']) ? $data['height'] : 0) *
            (isset($data['length']) ? $data['length'] : 0);
    }

    /**
     * Sorting function
     *
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    protected function _sort($a, $b)
    {
        $volumeA = $this->_getVolume($a);
        $volumeB = $this->_getVolume($b);

        return ($volumeA == $volumeB ? 0 : ($volumeA < $volumeB ? -1 : 1));
    }

    /**
     * Prepare data after loading
     *
     * @param DataObject $object
     * @return AbstractBackend
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $data = $object->getData($attributeCode);
        $data = (is_string($data) ? json_decode($data, true) : $data);

        $object->setData(
            $attributeCode,
            (empty($data) ? [] : $data)
        );

        return parent::afterLoad($object);
    }

    /**
     * Prepare data for save
     *
     * @param DataObject $object
     * @return AbstractBackend
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $data = $object->getData($attributeCode);
        if (is_array($data)) {
            usort($data, [$this, '_sort']);
        } else {
            $data = [];
        }

        $object->setData(
            $attributeCode,
            json_encode($data)
        );

        return parent::beforeSave($object);
    }

    /**
     * Validation
     *
     * @param DataObject $object
     * @return bool
     * @throws LocalizedException
     */
    public function validate($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $label = $this->getAttribute()->getFrontend()->getLabel();
        $data = $object->getData($attributeCode);
        if (is_array($data)) {
            foreach ($data as $_data) {
                if (!isset($_data['width']) || $_data['width'] <= 0 ||
                    !isset($_data['height']) || $_data['height'] <= 0 ||
                    !isset($_data['length']) || $_data['length'] <= 0 ||
                    !isset($_data['weight']) || $_data['weight'] <= 0
                ) {
                    throw new LocalizedException(
                        __('Incorrect value for the "%1" attribute. All dimensions should be defined and have a positive value.', $label)
                    );
                }
            }
        } elseif (!empty($data)) {
            throw new LocalizedException(
                __('Incorrect value for the "%1" attribute.', $label)
            );
        }

        return parent::validate($object);
    }
}
