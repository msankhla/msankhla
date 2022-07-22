<?php
/**
 * Copyright ©2019 Itegration Ltd., Inc. All rights reserved.
 * See COPYING.txt for license details.
 * @author: Perencz Tamás <tamas.perencz@itegraion.com>
 */

namespace Emartech\Emarsys\Model\Api;

use Emartech\Emarsys\Api\AttributesApiInterface;
use Emartech\Emarsys\Api\Data\AttributeInterface;
use Emartech\Emarsys\Api\Data\AttributeInterfaceFactory;
use Emartech\Emarsys\Api\Data\AttributesApiResponseInterface;
use Emartech\Emarsys\Api\Data\AttributesApiResponseInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection as CategoryAttributeCollection;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory as CategoryAttributeCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as ProductAttributeCollection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollectionFactory;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Collection as CustomerAddressAttributeCollection;
use Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory as
    CustomerAddressAttributeCollectionFactory;
use Magento\Customer\Model\ResourceModel\Attribute\Collection as CustomerAttributeCollection;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory as CustomerAttributeCollectionFactory;
use Magento\Eav\Model\Entity\Attribute as EavAttributeModel;
use Magento\Framework\Webapi\Exception as WebApiException;

class AttributesApi implements AttributesApiInterface
{
    /**
     * @var AttributesApiResponseInterfaceFactory
     */
    private $attributesApiResponseFactory;

    /**
     * @var ProductAttributeCollectionFactory
     */
    private $productAttributeCollectionFactory;

    /**
     * @var CategoryAttributeCollectionFactory
     */
    private $categoryAttributeCollectionFactory;

    /**
     * @var CustomerAttributeCollectionFactory
     */
    private $customerAttributeCollectionFactory;

    /**
     * @var CustomerAddressAttributeCollectionFactory
     */
    private $customerAddressAttributeCollectionFactory;

    /**
     * @var AttributeInterfaceFactory
     */
    private $attributeFactory;

    /**
     * @var null|CustomerAttributeCollection|ProductAttributeCollection|CategoryAttributeCollection|CustomerAddressAttributeCollection
     */
    private $attributeCollection = null;

    /**
     * AttributesApi constructor.
     *
     * @param AttributesApiResponseInterfaceFactory     $attributesApiResponseFactory
     * @param ProductAttributeCollectionFactory         $productAttributeCollectionFactory
     * @param CategoryAttributeCollectionFactory        $categoryAttributeCollectionFactory
     * @param CustomerAttributeCollectionFactory        $customerAttributeCollectionFactory
     * @param CustomerAddressAttributeCollectionFactory $customerAddressAttributeCollectionFactory
     * @param AttributeInterfaceFactory                 $attributeFactory
     */
    public function __construct(
        AttributesApiResponseInterfaceFactory $attributesApiResponseFactory,
        ProductAttributeCollectionFactory $productAttributeCollectionFactory,
        CategoryAttributeCollectionFactory $categoryAttributeCollectionFactory,
        CustomerAttributeCollectionFactory $customerAttributeCollectionFactory,
        CustomerAddressAttributeCollectionFactory $customerAddressAttributeCollectionFactory,
        AttributeInterfaceFactory $attributeFactory
    ) {
        $this->attributesApiResponseFactory = $attributesApiResponseFactory;
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->categoryAttributeCollectionFactory = $categoryAttributeCollectionFactory;
        $this->customerAttributeCollectionFactory = $customerAttributeCollectionFactory;
        $this->customerAddressAttributeCollectionFactory = $customerAddressAttributeCollectionFactory;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @param string $type
     *
     * @return AttributesApiResponseInterface
     * @throws WebApiException
     */
    public function get($type)
    {
        if (!in_array($type, self::TYPES)) {
            throw new WebApiException(__('Invalid Type'));
        }

        $this->initCollection($type);

        return $this->attributesApiResponseFactory->create()->setAttributes($this->handleAttributes());
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    private function initCollection($type)
    {
        switch ($type) {
            case AttributesApiInterface::TYPE_CATEGORY:
                $this->getCategoryAttributes();
                break;
            case AttributesApiInterface::TYPE_PRODUCT:
                $this->getProductAttributes();
                break;
            case AttributesApiInterface::TYPE_CUSTOMER_ADDRESS:
                $this->getCustomerAddressAttributes();
                break;
            case AttributesApiInterface::TYPE_CUSTOMER:
            default:
                $this->getCustomerAttributes();
                break;
        }

        return $this;
    }

    /**
     * @return AttributeInterface[]
     */
    private function handleAttributes()
    {
        $returnArray = [];
        if (null === $this->attributeCollection) {
            return $returnArray;
        }

        /** @var EavAttributeModel $attribute */
        foreach ($this->attributeCollection as $attribute) {
            if (null === $attribute->getFrontendLabel()) {
                continue;
            }

            $returnArray[] = $this->attributeFactory->create()
                ->setCode($attribute->getAttributeCode())
                ->setName($attribute->getFrontendLabel())
                ->setIsVisible($attribute->getIsVisible())
                ->setIsVisibleOnFront($attribute->getIsVisible() || $attribute->getIsVisibleOnFront())
                ->setIsSystem((bool)$attribute->getIsSystem());
        }

        return $returnArray;
    }

    /**
     * @return $this
     */
    private function getCustomerAttributes()
    {
        /** @var CustomerAttributeCollection attributeCollection */
        $this->attributeCollection = $this->customerAttributeCollectionFactory->create();
        $this->attributeCollection
            ->addFieldToFilter('entity_type_id', ['eq' => self::CUSTOMER_ENTITY_TYPE_ID]);

        return $this;
    }

    /**
     * @return $this
     */
    private function getCustomerAddressAttributes()
    {
        /** @var CustomerAddressAttributeCollection attributeCollection */
        $this->attributeCollection = $this->customerAddressAttributeCollectionFactory->create();
        $this->attributeCollection
            ->addFieldToFilter('entity_type_id', ['eq' => self::CUSTOMER_ADDRESS_ENTITY_TYPE_ID]);

        return $this;
    }

    /**
     * @return $this
     */
    private function getProductAttributes()
    {
        /** @var ProductAttributeCollection attributeCollection */
        $this->attributeCollection = $this->productAttributeCollectionFactory->create();
        $this->attributeCollection
            ->addFieldToFilter('entity_type_id', ['eq' => self::PRODUCT_ENTITY_TYPE_ID]);

        return $this;
    }

    /**
     * @return $this
     */
    private function getCategoryAttributes()
    {
        /** @var CategoryAttributeCollection attributeCollection */
        $this->attributeCollection = $this->categoryAttributeCollectionFactory->create();
        $this->attributeCollection
            ->addFieldToFilter('entity_type_id', ['eq' => self::CATEGORY_ENTITY_TYPE_ID]);

        return $this;
    }
}
