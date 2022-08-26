<?php

namespace Corra\Log\Controller\Adminhtml\Log\Archive;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Index
 *
 * Corra\Log\Controller\Adminhtml\Log\Archive
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return Page|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Corra_Log::log_archive');
        $resultPage->addBreadcrumb(__('Corra'), __('Corra'));
        $resultPage->addBreadcrumb(__('Manage item'), __('Log Archive'));
        $resultPage->getConfig()->getTitle()->prepend(__('Log Archive'));

        return $resultPage;
    }
}
