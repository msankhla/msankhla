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
namespace FedEx\CrossBorder\Model\ResourceModel;

use FedEx\CrossBorder\Helper\PackNotification as Helper;
use FedEx\CrossBorder\Model\PackNotification as Model;
use FedEx\CrossBorder\Model\PackNotification\Box;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\Item as ItemResource;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as SalesItemCollection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as SalesItemCollectionFactory;

use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\Collection as BoxCollection;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\CollectionFactory as BoxCollectionFactory;

class PackNotification extends AbstractDb
{
    const TABLE_NAME                = 'fdxcb_pack_notification';

    const ERROR_UNAVAILABLE         = 'You need the Merchant Control account and created invoices';
    const ERROR_CANT_BE_DELETE      = 'The record can\'t be deleted, because pack notification already sent';
    const ERROR_CANT_BE_SAVE        = 'The record can\'t be saved, because pack notification already sent';
    const ERROR_ORDER_ID            = 'Order ID not set';

    protected $_boxCollectionFactory;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var SalesItemCollectionFactory
     */
    protected $_salesItemCollectionFactory;

    /**
     * PackNotification constructor.
     *
     * @param BoxCollectionFactory $boxCollectionFactory
     * @param Helper $helper
     * @param SalesItemCollectionFactory $salesItemCollectionFactory
     * @param Context $context
     */
    public function __construct(
        BoxCollectionFactory $boxCollectionFactory,
        Helper $helper,
        SalesItemCollectionFactory $salesItemCollectionFactory,
        Context $context
    ) {
        $this->_boxCollectionFactory = $boxCollectionFactory;
        $this->_helper = $helper;
        $this->_salesItemCollectionFactory = $salesItemCollectionFactory;
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init(static::TABLE_NAME, 'entity_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var Box $box */
        foreach ($object->getBoxes() as $box) {
            if (!$box->getPackNotificationId()) {
                $box->setPackNotificationId(
                    $object->getId()
                );
            }
            if ($box->hasDataChanges()) {
                $box->save();
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
        if (!$this->getHelper()->isAvailable($object->getOrder())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(static::ERROR_UNAVAILABLE)
            );
        }

        if (!$object->canChange()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(static::ERROR_CANT_BE_DELETE)
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
        if (!$this->getHelper()->isAvailable($object->getOrder())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(static::ERROR_UNAVAILABLE)
            );
        }

        if (!$object->getOrderId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(static::ERROR_ORDER_ID)
            );
        }

        if (!$object->getDimensionUnit()) {
            $object->setDimensionUnit(
                $this->getHelper()->getDimensionUnit()
            );
        }

        if (!$object->getWeightUnit()) {
            $object->setWeightUnit(
                $this->getHelper()->getWeightUnit()
            );
        }

        if (!$object->getState()) {
            $object->setState(Model::STATE_NEW);

            if (!$object->getStatus()) {
                $object->setStatus(Model::STATUS_NEW);
            }
        }

        return parent::_beforeSave($object);
    }

    /**
     * Returns count of available order items
     *
     * @param Model $object
     * @return int
     */
    public function getAvailableItemCount($object)
    {
        $orderId = (int) $object->getOrderId();
        /** @var SalesItemCollection $collection */
        $collection = $this->_salesItemCollectionFactory->create();
        $collection->addFieldToSelect(
            'item_id'
        )->addFieldToFilter(
            'order_id',
            (int) $orderId
        )->addFieldToFilter(
            'product_type',
            'simple'
        )->addExpressionFieldToSelect(
            'qty',
            '((`main_table`.`qty_invoiced` - `main_table`.`qty_refunded`) - IFNULL(SUM(`items`.`qty`), 0))',
            []
        )->getSelect(
        )->joinLeft(
            ['items' => ItemResource::TABLE_NAME],
            '`main_table`.`item_id` = `items`.`order_item_id`',
            []
        )->group(
            'main_table.item_id'
        )->having('qty > 0');

        return count($collection);
    }

    /**
     * Returns box collection
     *
     * @param Model $object
     * @return BoxCollection
     */
    public function getBoxCollection($object)
    {
        /** @var BoxCollection $collection */
        $collection = $this->_boxCollectionFactory->create();
        $collection->addFieldToFilter(
            Box::PACK_NOTIFICATION_ID,
            (int) $object->getId()
        );

        return $collection;
    }

    /**
     * Returns helper
     *
     * @return Helper
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Load box list
     *
     * @param Model $object
     * @return Box []
     */
    public function loadBoxes($object)
    {
        $boxes = [];
        foreach ($this->getBoxCollection($object) as $box) {
            $boxes[$box->getId()] = $box;
        }

        return $boxes;
    }
}
