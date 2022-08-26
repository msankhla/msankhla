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
namespace FedEx\CrossBorder\Controller\Adminhtml\Scheduler;

use FedEx\CrossBorder\Api\Data\SchedulerInterface;
use FedEx\CrossBorder\Model\ResourceModel\Scheduler\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'FedEx_CrossBorder::scheduler';

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
     * Index constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Filter $filter,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_filter = $filter;
        $this->_logger = $logger;

        parent::__construct($context);
    }

    /**
     * Mass Delete Action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $itemsDeleted = 0;
        $itemsDeletedError = 0;
        /** @var SchedulerInterface $item */
        foreach ($collection->getItems() as $item) {
            try {
                $item->delete();
                $itemsDeleted++;
            } catch (LocalizedException $exception) {
                $this->_logger->error($exception->getLogMessage());
                $itemsDeletedError++;
            }
        }

        if ($itemsDeleted) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $itemsDeleted)
            );
        }

        if ($itemsDeletedError) {
            $this->messageManager->addErrorMessage(
                __(
                    'A total of %1 record(s) haven\'t been deleted. Please see server logs for more details.',
                    $itemsDeletedError
                )
            );
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}