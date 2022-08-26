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
namespace FedEx\CrossBorder\Ui\DataProvider\PackNotification;

use FedEx\CrossBorder\Helper\PackNotification as Helper;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\CollectionFactory;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\Item as ItemResource;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as SalesItemCollectionFactory;
use Magento\Framework\Registry;

class BoxForm extends Box
{
    /**
     * @var array
     */
    protected $_loadedData;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var SalesItemCollectionFactory
     */
    protected $_salesItemCollectionFactory;

    /**
     * BoxForm constructor.
     * @param Registry $registry
     * @param SalesItemCollectionFactory $salesItemCollectionFactory
     * @param CollectionFactory $collectionFactory
     * @param Helper $helper
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        SalesItemCollectionFactory $salesItemCollectionFactory,
        CollectionFactory $collectionFactory,
        Helper $helper,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_salesItemCollectionFactory = $salesItemCollectionFactory;
        parent::__construct($collectionFactory, $helper, $name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Preparing collection
     *
     * @return $this
     */
    public function prepareCollection()
    {
        $this->getCollection()->addFieldToFilter(
            'entity_id',
            $this->getHelper()->getId()
        );

        return parent::prepareCollection();
    }

    /**
     * Returns data
     *
     * @return array
     */
    public function getData()
    {
        if (!isset($this->_loadedData)) {
            $this->_loadedData = [];
            $orderId = $this->getHelper()->getOrderId();
            $packId = $this->getHelper()->getCurrentPackNotificationId();

            foreach ($this->getCollection() as $box) {
                /** @var \FedEx\CrossBorder\Model\PackNotification\Box $box */
                $id = $box->getId();
                $data = $box->getData();
                $data[$packId ? 'pack_id' : 'order_id'] = $packId ? $packId : $orderId;

                $data['data']['links']['item'] = [];

                foreach ($box->getItemCollection() as $item) {
                    $data['data']['links']['item'][] = [
                        'id'            => $item->getOrderItemId(),
                        'name'          => $item->getName(),
                        'sku'           => $item->getSku(),
                        'qty'           => $item->getQty(),
                    ];
                }

                $this->_loadedData[$id] = $data;
            }

            $data = $this->_registry->registry('box_form_data');
            if (!empty($data) || empty($this->_loadedData)) {
                $item = $this->getCollection()->getNewEmptyItem();
                $item->setData(!empty($data) ? $data : [($packId ? 'pack_id' : 'order_id') => $packId ? $packId : $orderId]);

                $this->_loadedData[$item->getId()] = $item->getData();
            }
        }

        return $this->_loadedData;
    }
}
