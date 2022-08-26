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
use Magento\Ui\DataProvider\AbstractDataProvider;

class Box extends AbstractDataProvider
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * Box constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param Helper $helper
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     *
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
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->prepareCollection();
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
        $this->getCollection()->addFieldToFilter(
            'pack_notification_id',
            $this->getHelper()->getCurrentPackNotificationId()
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
