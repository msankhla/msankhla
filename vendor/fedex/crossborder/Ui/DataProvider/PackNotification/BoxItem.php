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
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\Item as ItemResource;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class BoxItem extends AbstractDataProvider
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * BoxItem constructor.
     * @param CollectionFactory $collectionFactory
     * @param Helper $helper
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Helper $helper,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->_helper = $helper;

        $this->prepareCollection();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->prepareUrls();
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
     * Preparing collection
     *
     * @return $this
     */
    public function prepareCollection()
    {
        $table = ItemResource::TABLE_NAME;
        $this->getCollection()->addFieldToFilter(
            'order_id',
            $this->getHelper()->getOrderId()
        )->addFieldToFilter(
            'product_type',
            'simple'
        )->addExpressionFieldToSelect(
            'qty',
            '((`main_table`.`qty_invoiced` - `main_table`.`qty_refunded`) - IFNULL(SUM(`items`.`qty`), 0))',
            []
        )->getSelect(
        )->joinLeft(
            ['items' => $table],
            '`main_table`.`item_id` = `items`.`order_item_id` AND `items`.`box_id` <> ' . (int) $this->getHelper()->getId(),
            []
        )->group(
            'main_table.item_id'
        );

        return $this;
    }

    /**
     * Preparing urls
     *
     * @return $this
     */
    public function prepareUrls()
    {
        $this->data['config']['update_url'] = $this->getHelper()->getUrl('mui/index/render', array_merge(
            $this->getHelper()->getUrlParams(),
            ['id' => $this->getHelper()->getId()]
        ));

        return $this;
    }
}
