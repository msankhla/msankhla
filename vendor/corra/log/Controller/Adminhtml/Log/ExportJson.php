<?php

namespace Corra\Log\Controller\Adminhtml\Log;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context as ActionContext;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Exception;

/**
 * Class ExportJson
 *
 * Corra\Log\Controller\Adminhtml\Log
 */
class ExportJson extends Action
{
    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * ExportJson constructor.
     *
     * @param ActionContext $context
     * @param FileFactory $fileFactory
     */
    public function __construct(
        ActionContext $context,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws FileSystemException
     * @throws Exception
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'corra_log.json';
        /** @var \Corra\Log\Block\Adminhtml\Log\Grid $exportBlock */
        $exportBlock = $this->_view->getLayout()->getBlock('corra_log.log.grid');

        return $this->fileFactory->create(
            $fileName,
            $exportBlock->getJsonFile($fileName),
            DirectoryList::VAR_DIR
        );
    }
}
