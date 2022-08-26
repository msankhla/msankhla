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
use FedEx\CrossBorder\Model\OrderCancel\Sender;
use FedEx\CrossBorder\Model\OrderCancel\SenderFactory;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Collection as PackNotificationCollection;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\CollectionFactory as PackNotificationCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\RefundOrderInterface;
use Magento\Sales\Model\Order;

class OrderCancelManagement
{
    const ERROR                             = "The order can't be canceled. %1";
    const ERROR_ORDER_NOT_FOUND             = 'This order no longer exists.';
    const ERROR_ORDER_NOT_FEDEX             = 'The cancellation request can\'t be sent. The order should be created via FedEx Cross Border';
    const ERROR_UNAVAILABLE                 = 'You need the Merchant Control account';
    const MSG_CANCEL                        = 'The order canceled by FedEx Cross Border';
    const MSG_CANCELLATION_REQUEST_SENT     = 'The order cancellation request was sent.';

    const LOG_FILE                          = 'FedEx/CrossBorder/OrderCancel.log';

    /**
     * @var MerchantControl
     */
    protected $_merchantControl;

    /**
     * @var OrderManagementInterface
     */
    protected $_orderManagement;

    /**
     * @var PackNotificationCollectionFactory
     */
    protected $_packNotificationCollectionFactory;

    /**
     * @var RefundOrderInterface
     */
    protected $_refundOrder;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var SenderFactory
     */
    protected $_senderFactory;

    /**
     * OrderCancelManagement constructor.
     *
     * @param MerchantControl $merchantControl
     * @param OrderManagementInterface $orderManagement
     * @param PackNotificationCollectionFactory $packNotificationCollectionFactory
     * @param RefundOrderInterface $refundOrder
     * @param Registry $registry
     * @param SenderFactory $senderFactory
     */
    public function __construct(
        MerchantControl $merchantControl,
        OrderManagementInterface $orderManagement,
        PackNotificationCollectionFactory $packNotificationCollectionFactory,
        RefundOrderInterface $refundOrder,
        Registry $registry,
        SenderFactory $senderFactory
    ) {
        $this->_merchantControl = $merchantControl;
        $this->_orderManagement = $orderManagement;
        $this->_packNotificationCollectionFactory = $packNotificationCollectionFactory;
        $this->_refundOrder = $refundOrder;
        $this->_registry = $registry;
        $this->_senderFactory = $senderFactory;
    }

    /**
     * Adds log
     *
     * @param mixed $message
     * @return $this
     */
    public function addLog($message)
    {
        if ($this->isLogsEnabled()) {
            Log::Info($message, static::LOG_FILE);
        }

        return $this;
    }

    /**
     * Cancelling all order pack notifications
     *
     * @param Order $order
     * @return $this
     * @throws LocalizedException
     */
    public function cancelAllPackNotifications($order)
    {
        /** @var PackNotificationCollection $collection */
        $collection = $this->_packNotificationCollectionFactory->create();
        $collection->addFieldToFilter(PackNotification::ORDER_ID, $order->getId());
        /** @var PackNotification $item */
        foreach ($collection as $packNotification) {
            $this->cancelPackNotification($packNotification);
        }

        return $this;
    }

    /**
     * Cancelling pack notification
     *
     * @param PackNotification $packNotification
     * @return $this
     * @throws LocalizedException
     */
    public function cancelPackNotification(PackNotification $packNotification)
    {
        if ($packNotification && $packNotification->canCancel()) {
            $packNotification->cancel();
            $this->addLog(sprintf(
                'Pack Notification was cancelled (ID = %d)',
                $packNotification->getId()
            ));
        } else {
            $this->addLog(sprintf(
                'Pack Notification can\'t be cancelled (ID = %d)',
                $packNotification->getId()
            ));
        }

        return $this;
    }

    /**
     * Cancelling order
     *
     * @param Order $order
     * @return $this
     * @throws \Exception
     */
    public function execute($order)
    {
        $this->addLog(sprintf(
            'Cancelling process was started (Order ID = %d)',
            $order->getId()
        ));
        $this->_registry->register('skipRefundRequest', true);
        $this->cancelAllPackNotifications(
            $order
        )->refundOrder(
            $order
        );
        $this->_registry->unregister('skipRefundRequest');

        if ($order->canCancel()) {
            $this->_orderManagement->cancel($order->getId());
            $this->addLog(sprintf(
                'Order was cancelled (ID = %d)',
                $order->getId()
            ));
        } else {
            $this->addLog(sprintf(
                'Order can\'t be cancelled (ID = %d)',
                $order->getId()
            ));
        }

        $order->addCommentToStatusHistory(
            static::MSG_CANCEL
        )->save();

        $this->addLog(sprintf(
            'Cancelling process completed (Order ID = %d)',
            $order->getId()
        ));

        return $this;
    }

    /**
     * Checks if functionality available
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->_merchantControl->isEnabled();
    }

    /**
     * Checks if logs enabled
     *
     * @return bool
     */
    public function isLogsEnabled()
    {
        return (bool) $this->_merchantControl->getHelper()->isLogsEnabled();
    }

    /**
     * Refund order
     *
     * @param Order $order
     * @return $this
     */
    public function refundOrder($order)
    {
        if ($order->canCreditmemo()) {
            $this->_refundOrder->execute(
                $order->getId()
            );
            $this->addLog('The refunding was done');
        } else {
            $this->addLog('The refunding can\'t be done');
        }

        return $this;
    }

    /**
     * Sending cancellation request
     *
     * @param Order $order
     * @return $this
     * @throws LocalizedException
     */
    public function send($order)
    {
        $message = '';
        if ($this->isAvailable()) {
            if ($order && $order->getId()) {
                $extensionAttributes = $order->getExtensionAttributes();
                if ($extensionAttributes) {
                    /** @var OrderLinkInterface $orderLink */
                    $orderLink = $extensionAttributes->getFdxcbData();
                    if ($orderLink && $orderLink->getId()) {
                        $this->addLog(sprintf(
                            'Sending cancellation request process was started (Order ID = %d)',
                            $order->getId()
                        ));
                        try {
                            $this->cancelAllPackNotifications($order);

                            /** @var Sender $sender */
                            $sender = $this->_senderFactory->create();
                            $sender->addHeader(
                                'Content-Type',
                                'application/json'
                            )->addData(
                                json_encode([
                                    'order'    => [
                                        'id'                    => $order->getId(),
                                        'fxcb_order_number'     => $orderLink->getFxcbOrderNumber(),
                                    ]
                                ])
                            )->getResponse(
                                Sender::METHOD_POST
                            );

                            if (!$sender->hasError()) {
                                $order->setStatus(
                                    OrderStatusManagement::STATUS_CANCELLATION_REQUEST
                                )->addCommentToStatusHistory(
                                    static::MSG_CANCELLATION_REQUEST_SENT
                                );
                                $order->save();
                                $this->addLog(static::MSG_CANCELLATION_REQUEST_SENT);
                            } else {
                                $message = __(static::ERROR, $sender->getErrorMessage());
                            }
                        } catch (LocalizedException $e) {
                            $message = __(static::ERROR, $e->getMessage());
                        }  catch (\Exception $e) {
                            $message = __(static::ERROR, $e->getMessage());
                        }
                    } else {
                        $message = __(static::ERROR_ORDER_NOT_FEDEX);
                    }
                } else {
                    $message = __(static::ERROR_ORDER_NOT_FEDEX);
                }
            } else {
                $message = __(static::ERROR_ORDER_NOT_FOUND);
            }
        } else {
            $message = __(static::ERROR, __(static::ERROR_UNAVAILABLE));
        }


        if (!empty($message)) {
            throw new LocalizedException($message);
        }

        return $this;
    }
}
