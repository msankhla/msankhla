<?php

namespace Corra\Log\Block\Adminhtml\LogArchive;

use Magento\Backend\Block\Widget\Grid\Extended;
use Corra\Log\Block\Adminhtml\Log\Grid as LogGrid;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Helper\Data;
use Corra\Log\Model\LogFactory;
use Corra\Log\Model\LogArchiveFactory;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class Grid
 *
 * Corra\Log\Block\Adminhtml\LogArchive
 */
class Grid extends LogGrid
{
    /**
     * @var LogArchiveFactory
     */
    private $logArchiveFactory;

    /**
     * Grid constructor.
     *
     * @param TemplateContext $context
     * @param Data $backendHelper
     * @param LogFactory $logFactory
     * @param LogArchiveFactory $logArchiveFactory
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Data $backendHelper,
        LogFactory $logFactory,
        LogArchiveFactory $logArchiveFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $logFactory, $data);
        $this->logArchiveFactory = $logArchiveFactory;
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->logArchiveFactory->create()->getCollection();
        $this->setCollection($collection);

        Extended::_prepareCollection();

        return $this;
    }

    /**
     * @return array
     * @throws FileSystemException
     */
    public function getJsonFile()
    {
        $this->_isExport = true;
        $this->_prepareGrid();

        $name = md5(microtime());
        $file = $this->_path . '/' . $name . '.json';

        $this->_directory->create($this->_path);
        $stream = $this->_directory->openFile($file, 'w+');

        $stream->lock();
        $stream->write('{ "data": [');
        $this->_exportIterateCollection('exportJsonItem', [$stream]);

        $stream->write(']}');

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true // can delete file after use
        ];
    }
}
