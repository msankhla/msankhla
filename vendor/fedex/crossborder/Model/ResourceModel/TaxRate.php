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

use FedEx\CrossBorder\Model\TaxRate as Model;
use FedEx\CrossBorder\Model\ResourceModel\TaxRate\Collection;
use FedEx\CrossBorder\Model\ResourceModel\TaxRate\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class TaxRate extends AbstractDb
{
    const TABLE_NAME    = 'fdxcb_tax_rate';

    /** @var  Collection */
    protected $_collection;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var array
     */
    protected $_fields  = [
        Model::QUOTE_ID,
        Model::ITEM_ID,
        Model::SHIPPING_METHOD,
        Model::TAX_AMOUNT,
        Model::DUTY_AMOUNT,
    ];

    /**
     * TaxRate constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Context $context
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init(static::TABLE_NAME, 'entity_id');
    }

    /**
     * Adds new item tax
     *
     * @param array $data
     * @return $this
     * @throws LocalizedException
     */
    public function addItemTax($data)
    {
        if (empty($data['quote_id'])) {
            throw new LocalizedException(__('Quote ID not defined'));
        }

        if (empty($data['item_id'])) {
            throw new LocalizedException(__('Item ID not defined'));
        }

        if (empty($data['shipping_method'])) {
            throw new LocalizedException(__('Shipping method not defined'));
        }

        $this->getConnection()->insertOnDuplicate(
            static::TABLE_NAME,
            $data,
            $this->_fields
        );

        return $this;
    }

    /**
     * Returns collection
     *
     * @param bool $isNew
     * @return Collection
     */
    public function getCollection($isNew = false)
    {
        if (!isset($this->_collection) || $isNew) {
            $this->_collection = $this->_collectionFactory->create();
        }

        return $this->_collection;
    }

    /**
     * Clear by quote and shipping method
     *
     * @param int $quoteId
     * @param string|null $shippingMethod
     * @return $this
     */
    public function clearByQuote($quoteId, $shippingMethod = null)
    {
        if (!empty($quoteId)) {
            $this->getConnection()->delete(
                static::TABLE_NAME,
                Model::QUOTE_ID . ' = ' . intval($quoteId) .
                (isset($shippingMethod) ? ' AND  ' . Model::SHIPPING_METHOD . ' = "' . $shippingMethod . '"' : '')
            );
        }

        return $this;
    }

    /**
     * Load item data
     *
     * @param Model $object
     * @param int $itemId
     * @param string $shippingMethod
     * @return $this
     */
    public function loadItemData(
        Model $object,
        $itemId,
        $shippingMethod
    ) {
        if (!empty($itemId) && !empty($shippingMethod) && $connection = $this->getConnection()) {
            $select = $connection->select(
            )->from(
                static::TABLE_NAME
            )->where(
                Model::ITEM_ID . ' = ?',
                (int) $itemId
            )->where(
                Model::SHIPPING_METHOD . ' = ?',
                $shippingMethod
            );

            if ($data = $this->getConnection()->fetchRow($select)) {
                $object->setData($data);
            }
        }

        return $this;
    }
}