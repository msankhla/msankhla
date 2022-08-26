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
namespace FedEx\CrossBorder\Controller\Adminhtml\PackNotification;

use FedEx\CrossBorder\Controller\Adminhtml\PackNotification as PackNotificationAction;
use FedEx\CrossBorder\Helper\PackNotification as Helper;
use FedEx\CrossBorder\Model\PackNotification;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class MassSend extends PackNotificationAction
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * MassSend constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param LoggerInterface $logger
     * @param Helper $helper
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Filter $filter,
        LoggerInterface $logger,
        Helper $helper,
        Registry $registry,
        Context $context
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_filter = $filter;
        $this->_logger = $logger;

        parent::__construct($helper, $registry, $context);
    }

    /**
     * Mass Delete Action
     */
    public function execute()
    {
        if ($this->isValid()) {
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $itemsSent = 0;
            $itemsSentError = 0;
            /** @var PackNotification $item */
            foreach ($collection->getItems() as $item) {
                try {
                    $item->send();
                    $itemsSent++;
                } catch (LocalizedException $exception) {
                    $this->_logger->error($exception->getLogMessage());
                    $itemsSentError++;
                }
            }

            if ($itemsSent) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 pack notification(s) have been sent.', $itemsSent)
                );
            }

            if ($itemsSentError) {
                $this->messageManager->addErrorMessage(
                    __(
                        'A total of %1 pack notification(s) haven\'t been sent. Please see server logs for more details.',
                        $itemsSentError
                    )
                );
            }

            return $this->_redirect('sales/order/view', [
                'order_id'      => $this->_helper->getOrderId(),
                'active_tab'    => 'order_pack_notification',
            ]);
        }

        return $this->_redirect('sales/order/view', ['order_id' => $this->_helper->getOrderId()]);
    }
}
