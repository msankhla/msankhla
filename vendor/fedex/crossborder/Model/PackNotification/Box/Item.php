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
namespace FedEx\CrossBorder\Model\PackNotification\Box;

use FedEx\CrossBorder\Helper\Data as Helper;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\Item as ResourceModel;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Item extends AbstractModel
{
    const BOX_ID            = 'box_id';
    const PRODUCT_ID        = 'product_id';
    const ORDER_ITEM_ID     = 'order_item_id';
    const QTY               = 'qty';
    const COUNTRY_OF_ORIGIN = 'country_of_origin';

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var ProductInterface
     */
    protected $_product;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * Item constructor.
     *
     * @param Helper $helper
     * @param ProductRepositoryInterface $productRepository
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Helper $helper,
        ProductRepositoryInterface $productRepository,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_productRepository = $productRepository;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Returns box id
     *
     * @return int
     */
    public function getBoxId()
    {
        return $this->getData(static::BOX_ID);
    }

    /**
     * Sets box id
     *
     * @param int $value
     * @return $this
     */
    public function setBoxId($value)
    {
        return $this->setData(static::BOX_ID, $value);
    }

    /**
     * Returns product
     *
     * @return ProductInterface
     */
    public function getProduct()
    {
        if (!isset($this->_product)) {
            $this->_product = $this->_productRepository->getById(
                $this->getProductId()
            );
        }

        return $this->_product;
    }

    /**
     * Returns product id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getData(static::PRODUCT_ID);
    }

    /**
     * Returns product identifier value
     *
     * @return mixed
     */
    public function getProductIdentifier()
    {
        return $this->getProduct()->getData(
            $this->_helper->getProductIdentifier()
        );
    }

    /**
     * Sets product id
     *
     * @param int $value
     * @return $this
     */
    public function setProductId($value)
    {
        return $this->setData(static::PRODUCT_ID, $value);
    }

    /**
     * Returns order item id
     *
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->getData(static::ORDER_ITEM_ID);
    }

    /**
     * Sets order item id
     *
     * @param int $value
     * @return $this
     */
    public function setOrderItemId($value)
    {
        return $this->setData(static::ORDER_ITEM_ID, $value);
    }

    /**
     * Returns qty
     *
     * @return float
     */
    public function getQty()
    {
        return $this->getData(static::QTY);
    }

    /**
     * Sets qty
     *
     * @param float $value
     * @return $this
     */
    public function setQty($value)
    {
        return $this->setData(static::QTY, $value);
    }

    /**
     * Returns country_of_origin
     *
     * @return float
     */
    public function getCountryOfOrigin()
    {
        return $this->getData(static::COUNTRY_OF_ORIGIN);
    }

    /**
     * Sets country_of_origin
     *
     * @param float $value
     * @return $this
     */
    public function setCountryOfOrigin($value)
    {
        return $this->setData(static::COUNTRY_OF_ORIGIN, $value);
    }
}