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
namespace FedEx\CrossBorder\Model\ResourceModel\PackNotification;

use FedEx\CrossBorder\Model\PackNotification;
use FedEx\CrossBorder\Model\PackNotificationFactory;
use FedEx\CrossBorder\Model\PackNotification\Box as BoxModel;
use FedEx\CrossBorder\Model\PackNotification\Box\Item;
use FedEx\CrossBorder\Model\ProductValidator;
use  FedEx\CrossBorder\Model\ResourceModel\PackNotification as PackNotificationResource;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\Item as ItemResource;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\Item\Collection as ItemCollection;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Eav\Model\Config;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as SalesItemCollectionFactory;

class Box extends AbstractDb
{
    const TABLE_NAME                    = 'fdxcb_pack_notification_box';
    const ERROR_PACK_NOTIFICATION_ID    = 'Pack notification ID not set.';
    const ERROR_NO_ITEMS                = 'The list of items can\'t be empty.';
    const ERROR_WIDTH                   = 'Incorrect width value.';
    const ERROR_HEIGHT                  = 'Incorrect height value.';
    const ERROR_LENGTH                  = 'Incorrect length value.';
    const ERROR_WEIGHT                  = 'Incorrect weight value.';

    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var ItemCollectionFactory
     */
    protected $_itemCollectionFactory;

    /**
     * @var PackNotificationFactory
     */
    protected $_packNotificationFactory;

    /**
     * @var SalesItemCollectionFactory
     */
    protected $_salesItemCollectionFactory;

    /**
     * Box constructor.
     *
     * @param Config $eavConfig
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param PackNotificationFactory $packNotificationFactory
     * @param SalesItemCollectionFactory $salesItemCollectionFactory
     * @param Context $context
     */
    public function __construct(
        Config $eavConfig,
        ItemCollectionFactory $itemCollectionFactory,
        PackNotificationFactory $packNotificationFactory,
        SalesItemCollectionFactory $salesItemCollectionFactory,
        Context $context
    ) {
        $this->_eavConfig = $eavConfig;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_packNotificationFactory = $packNotificationFactory;
        $this->_salesItemCollectionFactory = $salesItemCollectionFactory;
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init(static::TABLE_NAME, BoxModel::ENTITY_ID);
    }

    /**
     * Adds attribute into collection
     *
     * @param AbstractCollection $collection
     * @param AbstractAttribute $attribute
     * @return $this
     */
    protected function _addAttributeToCollection(
        AbstractCollection $collection,
        AbstractAttribute $attribute
    ) {
        if ($attribute->getId()) {
            $tableAlias = $attribute->getAttributeCode() . '_table';
            $entityIdField = $attribute->getEntityIdField();
            // Default value
            $alias = $tableAlias . '_def';
            $condition = '`main_table`.`product_id`  = `' . $alias . '`.`' . $entityIdField . '` AND ' .
                '`' . $alias . '`.`attribute_id` = ' . $attribute->getId() . ' AND ' .
                '`' . $alias . '`.`store_id` = 0';
            $collection->getSelect()->joinLeft(
                [$alias  => $attribute->getBackendTable()],
                $condition,
                []
            );

            // Current store value
            $alias = $tableAlias;
            $condition = '`main_table`.`product_id`  = `' . $alias . '`.`' . $entityIdField . '` AND ' .
                '`' . $alias . '`.`attribute_id` = ' . $attribute->getId() . ' AND ' .
                '`' . $alias . '`.`store_id` = `main_table`.`store_id`';
            $collection->getSelect()->joinLeft(
                [$alias  => $attribute->getBackendTable()],
                $condition,
                []
            );

            $collection->addExpressionFieldToSelect(
                $attribute->getAttributeCode(),
                'IFNULL(`' . $tableAlias . '`.`value`, `' . $tableAlias . '_def`.`value`)',
                []
            );
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setItems(
            $this->loadItems($object)
        );
        $object->setItemIds(
            $object->getAllItemIds()
        );

        return parent::_afterLoad($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $removeIds = (array) $object->getRemoveIds();

        /** @var Item $item */
        foreach ($object->getItems() as $id => $item) {
            if (!in_array($id, $removeIds)) {
                if (!$item->getBoxId()) {
                    $item->setBoxId(
                        $object->getId()
                    );
                }
                if ($item->hasDataChanges()) {
                    $item->save();
                }
            } else {
                $item->delete();
            }
        }

        return parent::_afterSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->canChange()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(PackNotificationResource::ERROR_CANT_BE_DELETE)
            );
        }

        return parent::_beforeDelete($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getPackNotificationId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(static::ERROR_PACK_NOTIFICATION_ID)
            );
        }

        if ($object->getWidth() <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(static::ERROR_WIDTH)
            );
        }

        if ($object->getHeight() <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(static::ERROR_HEIGHT)
            );
        }

        if ($object->getLength() <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(static::ERROR_LENGTH)
            );
        }

        if ($object->getWeight() <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(static::ERROR_WEIGHT)
            );
        }

        if (!count($object->getItems())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(static::ERROR_NO_ITEMS)
            );
        }

        if (!$object->canChange()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(PackNotificationResource::ERROR_CANT_BE_SAVE)
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     * Returns item collection
     *
     * @param BoxModel $object
     * @return ItemCollection
     */
    public function getItemCollection($object)
    {
        /** @var ItemCollection $collection */
        $collection = $this->_itemCollectionFactory->create();
        $collection->addFieldToFilter(
            Item::BOX_ID,
            (int) $object->getId()
        )->getSelect(
        )->joinLeft(
            ['items' => 'sales_order_item'],
            '`main_table`.`order_item_id` = `items`.`item_id`',
            ['name', 'sku']
        );

        return $collection;
    }

    /**
     * Returns collection of available order items
     *
     * @param BoxModel $box
     * @param null $orderId
     * @return ItemCollection
     */
    public function getAvailableItemCollection(BoxModel $box, $orderId = null)
    {
        $table = ItemResource::TABLE_NAME;
        /** @var ItemCollection $collection */
        $collection = $this->_salesItemCollectionFactory->create();
        if (!empty($orderId)) {
            $collection->addFieldToFilter(
                'order_id',
                (int) $orderId
            );
        }
        $collection->addFieldToFilter(
            'product_type',
            'simple'
        )->addExpressionFieldToSelect(
            'qty',
            '((`main_table`.`qty_invoiced` - `main_table`.`qty_refunded`) - IFNULL(SUM(`items`.`qty`), 0))',
            []
        )->getSelect(
        )->joinLeft(
            ['items' => $table],
            '`main_table`.`item_id` = `items`.`order_item_id` AND `items`.`box_id` <> ' . (int) $box->getId(),
            []
        )->group(
            'main_table.item_id'
        );

        $this->_addAttributeToCollection(
            $collection,
            $this->_eavConfig->getAttribute(Product::ENTITY, ProductValidator::COO_ATTRIBUTE_CODE)
        );

        return $collection;
    }

    /**
     * Returns eav config
     *
     * @return Config
     */
    public function getEavConfig()
    {
        return $this->_eavConfig;
    }

    /**
     * Returns pack notification model
     *
     * @param BoxModel $object
     * @return PackNotification
     */
    public function getPackNotification($object)
    {
        return $this->_packNotificationFactory->create()->load(
            $object->getPackNotificationId()
        );
    }

    /**
     * Load box items
     *
     * @param BoxModel $object
     * @return Item []
     */
    public function loadItems($object)
    {
        $items = [];
        foreach ($this->getItemCollection($object) as $item) {
            $items[$item->getOrderItemId()] = $item;
        }

        return $items;
    }
}