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
namespace FedEx\CrossBorder\Model\PackNotification;

use FedEx\CrossBorder\Model\PackNotification;
use FedEx\CrossBorder\Model\PackNotification\Box\Item;
use FedEx\CrossBorder\Model\PackNotification\Box\ItemFactory;
use FedEx\CrossBorder\Model\ProductValidator;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\Item\Collection as ItemCollection;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box as ResourceModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Box extends AbstractModel
{
    const ENTITY_ID             = 'entity_id';
    const PACK_NOTIFICATION_ID  = 'pack_notification_id';
    const WIDTH                 = 'width';
    const HEIGHT                = 'height';
    const LENGTH                = 'length';
    const WEIGHT                = 'weight';

    /**
     * @var ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var Item[]
     */
    protected $_items           = [];

    /**
     * @var array
     */
    protected $_origItemsIds    =   [];

    /**
     * @var PackNotification
     */
    protected $_packNotification;

    /**
     * Box constructor.
     *
     * @param ItemFactory $itemFactory
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ItemFactory $itemFactory,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_itemFactory = $itemFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Returns pack notification id
     *
     * @return int
     */
    public function getPackNotificationId()
    {
        return $this->getData(static::PACK_NOTIFICATION_ID);
    }

    /**
     * Sets pack notification id
     *
     * @param int $value
     * @return $this
     */
    public function setPackNotificationId($value)
    {
        return $this->setData(static::PACK_NOTIFICATION_ID, $value);
    }

    /**
     * Returns width
     *
     * @return float
     */
    public function getWidth()
    {
        return $this->getData(static::WIDTH);
    }

    /**
     * Sets width
     *
     * @param float $value
     * @return $this
     */
    public function setWidth($value)
    {
        return $this->setData(static::WIDTH, $value);
    }

    /**
     * Returns height
     *
     * @return float
     */
    public function getHeight()
    {
        return $this->getData(static::HEIGHT);
    }

    /**
     * Sets height
     *
     * @param float $value
     * @return $this
     */
    public function setHeight($value)
    {
        return $this->setData(static::HEIGHT, $value);
    }

    /**
     * Returns length
     *
     * @return float
     */
    public function getLength()
    {
        return $this->getData(static::LENGTH);
    }

    /**
     * Sets length
     *
     * @param float $value
     * @return $this
     */
    public function setLength($value)
    {
        return $this->setData(static::LENGTH, $value);
    }

    /**
     * Returns weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->getData(static::WEIGHT);
    }

    /**
     * Sets weight
     *
     * @param float $value
     * @return $this
     */
    public function setWeight($value)
    {
        return $this->setData(static::WEIGHT, $value);
    }

    /**
     * Adds items
     *
     * @param array $data
     * @return $this
     */
    public function addItems($data)
    {
        $this->_hasDataChanges = true;
        $ids = [];
        foreach ($data as $itemData) {
            $item = $this->getItem($itemData['id']);
            if (!$item->getId()) {
                $item->addData([
                    'order_item_id' => $itemData['id'],
                    'qty'           => $itemData['qty'],
                ]);
                $this->_items[$item->getOrderItemId()] = $item;
            } else {
                $item->setQty($itemData['qty']);
            }
            $ids[] = $item->getOrderItemId();
        };

        $removeItems = (array_diff(
            $this->getAllItemIds(),
            $ids
        ));

        if (!empty($ids)) {
            /** @var ItemCollection $collection */
            $collection = $this->getResource()->getAvailableItemCollection($this);
            $collection->addFieldToFilter(
                'item_id',
                $ids
            );

            foreach ($collection as $itemData) {
                $item = $this->getItem(
                    $itemData->getItemId()
                );

                if ($item->getQty() > $itemData->getQty()) {
                    $item->setQty(
                        $itemData->getQty()
                    );
                }

                if ($item->getQty() > 0) {
                    $item->setProductId(
                        $itemData->getProductId()
                    )->setCountryOfOrigin(
                        $itemData->getData(ProductValidator::COO_ATTRIBUTE_CODE)
                    );
                } else {
                    $removeItems[] = $item->getOrderItemId();
                }
            }
        }

        $this->removeItem($removeItems);

        return $this;
    }

    /**
     * Checks if box can be changed
     *
     * @return bool
     */
    public function canChange()
    {
        return $this->getPackNotification()->canChange();
    }

    /**
     * Returns all item ids
     *
     * @return array
     */
    public function getAllItemIds()
    {
        return array_keys($this->getItems());
    }

    /**
     * Create empty item
     *
     * @return Item
     */
    public function getEmptyItem()
    {
        /** @var Item $item */
        $item = $this->_itemFactory->create();
        $item->setBoxId(
            $this->getId()
        );

        return $item;
    }

    /**
     * Returns items by item_id
     *
     * @param int $id
     * @return Item
     */
    public function getItem($id)
    {
        return (isset($this->_items[$id]) ? $this->_items[$id] : $this->getEmptyItem());
    }

    /**
     * Returns all items
     *
     * @return Item[]
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Returns items collection
     *
     * @return ItemCollection
     */
    public function getItemCollection()
    {
        return $this->getResource()->getItemCollection($this);
    }

    /**
     * Returns pack notification
     *
     * @return PackNotification
     */
    public function getPackNotification()
    {
        if (!isset($this->_packNotification)) {
            $this->setPackNotification(
                $this->getResource()->getPackNotification($this)
            );
        }

        return $this->_packNotification;
    }

    /**
     * Remove item by item_id
     *
     * @param int|array $id
     * @return $this
     */
    public function removeItem($id)
    {
        $id = (array) $id;
        if (!empty($id)) {
            $this->_hasDataChanges = true;
        }
        foreach ($id as $_id) {
            unset($this->_items[$_id]);
        }

        return $this;
    }

    /**
     * @param Item[] $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->_hasDataChanges = true;
        $this->_items = $items;

        return $this;
    }

    /**
     * Sets pack notification
     *
     * @param PackNotification $packNotification
     * @return $this
     */
    public function setPackNotification(PackNotification $packNotification)
    {
        $this->_packNotification = $packNotification;

        return $this;
    }
}