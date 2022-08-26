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
namespace FedEx\CrossBorder\Model;

use FedEx\Core\Model\Log;
use FedEx\CrossBorder\Api\Data\OrderLinkInterface;
use FedEx\CrossBorder\Api\OrderStatusManagementInterface;
use FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformationInterface;
use FedEx\CrossBorder\Api\Data\OrderManagement\ReadyForExport\OrderInformationInterface as ReadyForExportOrderInformationInterface;
use FedEx\CrossBorder\Helper\Data as Helper;
use FedEx\CrossBorder\Model\OrderCancelManagementFactory;
use FedEx\CrossBorder\Model\OrderLinkFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

class OrderStatusManagement implements OrderStatusManagementInterface
{
    const COMMENT_VEND                  = 'FedEx Cross Border ready';
    const ERROR_CANT_BE_UPDATED         = 'The order status can\'t be updated';
    const ERROR_INCORRECT_STATUS        = 'Incorrect status value';
    const ERROR_ORDER_EXIST             = 'The order does not exist';
    const LOG_FILE                      = 'FedEx/CrossBorder/OrderStatus.log';
    const STATUS_READY_FOR_EXPORT       = 'fdxcb_ready_for_export';
    const STATUS_CANCELLATION_REQUEST   = 'fdxcb_cancellation_request';

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var OrderCancelManagementFactory
     */
    protected $_orderCancelManagementFactory;

    /**
     * @var OrderLinkFactory
     */
    protected $_orderLinkFactory;

    /**
     * @var Result
     */
    protected $_result;

    /**
     * @var array
     */
    protected $_statusMapping = [
        'v' => [
            'state'     => Order::STATE_PROCESSING,
            'status'    => '',
        ],
        'z' => [
            'state'     => Order::STATE_PROCESSING,
            'status'    => self::STATUS_READY_FOR_EXPORT,
        ],
    ];

    /**
     * OrderStatusManagement constructor.
     *
     * @param Helper $helper
     * @param OrderCancelManagementFactory $orderCancelManagementFactory
     * @param OrderLinkFactory $orderLinkFactory
     * @param Result $result
     */
    public function __construct(
        Helper $helper,
        OrderCancelManagementFactory $orderCancelManagementFactory,
        OrderLinkFactory $orderLinkFactory,
        Result $result
    ) {
        $this->_helper = $helper;
        $this->_orderCancelManagementFactory = $orderCancelManagementFactory;
        $this->_orderLinkFactory = $orderLinkFactory;
        $this->_result = $result;
    }

    /**
     * Adds log
     *
     * @param mixed $message
     * @return $this
     */
    public function addLog($message)
    {
        if ($this->_helper->isLogsEnabled()) {
            Log::Info($message, static::LOG_FILE);
        }

        return $this;
    }

    /**
     * Checks if order can be updated
     *
     * @param Order $order
     * @return bool
     */
    public function canUpdate($order)
    {
        return !in_array($order->getState(), [Order::STATE_CANCELED, Order::STATE_CLOSED, Order::STATE_COMPLETE]);
    }

    /**
     * Converting FedEx order status into Magento order status
     *
     * @param string $status
     * @return string
     */
    public function convertStatus($status)
    {
        $status = strtolower($status);
        return (isset($this->_statusMapping[$status]) ? $this->_statusMapping[$status]['status'] : '');
    }

    /**
     * Converting FedEx order status into Magento order state
     *
     * @param string $status
     * @return string
     */
    public function convertState($status)
    {
        return (isset($this->_statusMapping[$status]) ? $this->_statusMapping[$status]['state'] : '');
    }

    /**
     * Update order
     *
     * @param OrderInformationInterface $orderInformation
     * @return Result
     */
    public function update(
        OrderInformationInterface $orderInformation
    ) {
        $this->_result->reset();
        try {
            $this->addLog('[DATA] Order data received: ' . print_r($orderInformation->toArray(), true));
            $fdxcbStatus = strtolower($orderInformation->getStatus());
            $state = $this->convertState($fdxcbStatus);
            /** @var OrderLinkInterface $orderLink */
            $orderLink = $this->_orderLinkFactory->create()->load(
                $orderInformation->getOrderId(),
                OrderLinkInterface::FXCB_ORDER_NUMBER
            );
            /** @var \Magento\Sales\Model\Order $order */
            $order = $orderLink->getOrder(true);

            if (!$order->getId()) {
                throw new LocalizedException(
                    __(static::ERROR_ORDER_EXIST)
                );
            }

            if ($this->canUpdate($order)) {
                if (!empty($state)) {
                    $status = $this->convertStatus($orderInformation->getStatus());
                    if (empty($status)) {
                        $status = $order->getConfig()->getStateDefaultStatus($state);
                    }
                    $order->setState(
                        $state
                    )->setStatus(
                        $status
                    )->addCommentToStatusHistory(
                        static::COMMENT_VEND
                    );
                    $order->save();
                } else {
                    $comment = $orderInformation->getComment();
                    if ($fdxcbStatus == 'c') {
                        /** @var OrderCancelManagement $orderCancelManagement */
                        $orderCancelManagement = $this->_orderCancelManagementFactory->create();
                        $orderCancelManagement->execute($order);
                    } elseif ($fdxcbStatus == 'q' && !empty($comment)) {
                        $order->addCommentToStatusHistory(
                            $comment
                        )->save();
                    }
                }

                $orderLink->setStatus(
                    $orderInformation->getStatus()
                )->save();

                $this->addLog(sprintf(
                    '[SUCCESS]: Order Status Updated (Order ID = %s; FedEx Status = "%s"; State = "%s"; Status = "%s")',
                    $order->getId(),
                    $orderInformation->getStatus(),
                    $order->getState(),
                    $order->getStatus()
                ));
            }
        } catch (LocalizedException $e) {
            $this->_result->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->_result->addErrorMessage($e->getMessage());
        }

        if ($this->_result->getStatus() == Result::STATUS_ERROR) {
            $this->addLog('[ERROR]: ' . $this->_result->getMessage());
        }

        return $this->_result;
    }

    /**
     * Updating status to "Ready for Export"
     *
     * @param ReadyForExportOrderInformationInterface $orderInformation
     * @return Result
     */
    public function readyForExport(
        ReadyForExportOrderInformationInterface $orderInformation
    ) {
        $this->_result->reset();

        try {
            $this->addLog('[DATA] Order data received: ' . print_r($orderInformation->toArray(), true));
            $fdxcbStatus = strtolower($orderInformation->getStatus());
            $state = $this->convertState($fdxcbStatus);
            /** @var OrderLinkInterface $orderLink */
            $orderLink = $this->_orderLinkFactory->create()->load(
                $orderInformation->getInformation()->getIdorder(),
                OrderLinkInterface::FXCB_ORDER_NUMBER
            );
            /** @var \Magento\Sales\Model\Order $order */
            $order = $orderLink->getOrder(true);

            if (!$order->getId()) {
                throw new LocalizedException(
                    __(static::ERROR_ORDER_EXIST)
                );
            }

            if (!$this->canUpdate($order)) {
                throw new LocalizedException(
                    __(static::ERROR_CANT_BE_UPDATED)
                );
            }

            if (!empty($state)) {
                $status = $this->convertStatus($orderInformation->getStatus());
                if (empty($status)) {
                    $status = $order->getConfig()->getStateDefaultStatus($state);
                }
                $order->setState(
                    $state
                )->setStatus(
                    $status
                );
                $order->save();
            } else {
                throw new LocalizedException(
                    __(static::ERROR_INCORRECT_STATUS)
                );
            }

            $orderLink->setStatus(
                $orderInformation->getStatus()
            )->save();

            $this->addLog(sprintf(
                '[SUCCESS]: Order Status Updated (Order ID = %s; FedEx Status = "%s"; State = "%s"; Status = "%s")',
                $order->getId(),
                $orderInformation->getStatus(),
                $order->getState(),
                $order->getStatus()
            ));
        } catch (LocalizedException $e) {
            $this->_result->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->_result->addErrorMessage($e->getMessage());
        }

        if ($this->_result->getStatus() == Result::STATUS_ERROR) {
            $this->addLog('[ERROR]: ' . $this->_result->getMessage());
        }

        return $this->_result;
    }
}