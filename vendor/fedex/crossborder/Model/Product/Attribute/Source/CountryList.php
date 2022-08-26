<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FedEx\CrossBorder\Model\Product\Attribute\Source;

use Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture;
use Magento\Directory\Model\CountryFactory;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;

class CountryList extends Countryofmanufacture
{
    /**
     * @var AttributeFactory
     */
    protected $_eavAttrEntity;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * CountryList constructor.
     *
     * @param AttributeFactory $eavAttrEntity
     * @param CountryFactory $countryFactory
     * @param StoreManagerInterface $storeManager
     * @param Config $configCacheType
     */
    public function __construct(
        AttributeFactory $eavAttrEntity,
        CountryFactory $countryFactory,
        StoreManagerInterface $storeManager,
        Config $configCacheType
    ) {
        $this->_eavAttrEntity = $eavAttrEntity;
        parent::__construct($countryFactory, $storeManager, $configCacheType);
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $isMultiselect = $this->getAttribute()->getFrontend()->getInputType() == 'multiselect';

        return [
            $attributeCode => [
                'unsigned'  => false,
                'default'   => null,
                'extra'     => null,
                'type'      => Table::TYPE_TEXT,
                'length'    => ($isMultiselect ? 255 : 3),
                'nullable'  => true,
                'comment'   => $attributeCode . ' column',
            ]
        ];
    }

    /**
     * Retrieve Indexes(s) for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = [];

        $index = 'IDX_' . strtoupper($this->getAttribute()->getAttributeCode());
        $indexes[$index] = ['type' => 'index', 'fields' => [$this->getAttribute()->getAttributeCode()]];

        return $indexes;
    }


    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->_eavAttrEntity->create()->getFlatUpdateSelect($this->getAttribute(), $store);
    }

    /**
     * Get serializer
     *
     * @return SerializerInterface
     * @deprecated 102.0.0
     */
    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = ObjectManager::getInstance()
                ->get(SerializerInterface::class);
        }

        return $this->serializer;
    }
}
