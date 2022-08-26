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
namespace FedEx\CrossBorder\Model\ResourceModel\Order\Grid;

use FedEx\CrossBorder\Api\Data\OrderLinkInterface;
use FedEx\CrossBorder\Model\ResourceModel\OrderLink;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order;
use Psr\Log\LoggerInterface as Logger;

class Collection extends OrderCollection
{
    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_order_grid',
        $resourceModel = Order::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Hook for operations before rendering filters
     */
    protected function _renderFiltersBefore()
    {
        $joinTable = $this->getTable(OrderLink::TABLE_NAME);
        $this->getSelect()->joinLeft(
            $joinTable,
            'main_table.entity_id = ' . $joinTable . '.order_id',
            [OrderLinkInterface::FXCB_ORDER_NUMBER, OrderLinkInterface::STATUS]
        );

        parent::_renderFiltersBefore();
    }
}