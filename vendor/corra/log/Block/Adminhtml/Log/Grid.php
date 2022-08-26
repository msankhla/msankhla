<?php

namespace Corra\Log\Block\Adminhtml\Log;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem\File\WriteInterface;
use Psr\Log\LogLevel;
use Corra\Log\Model\LogStatus;
use Corra\Log\Model\LogFactory;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Backend\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class Grid
 *
 * Corra\Log\Block\Adminhtml\Log
 */
class Grid extends Extended
{
    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * Grid constructor.
     *
     * @param TemplateContext $context
     * @param Data $backendHelper
     * @param LogFactory $logFactory
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Data $backendHelper,
        LogFactory $logFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->logFactory = $logFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
        $this->addExportType('*/*/exportJson', 'JSON');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->logFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return Extended
     * @throws LocalizedException
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'type' => 'datetime',
            ]
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type',
            ]
        );

        $this->addColumn(
            'subtype',
            [
                'header' => __('Subtype'),
                'index' => 'subtype',
            ]
        );

        $this->addColumn(
            'level',
            [
                'header' => __('Severity'),
                'index' => 'level',
                'type' => 'options',
                'options' => [
                    LogLevel::EMERGENCY => __('Emergency'),
                    LogLevel::ALERT => __('Alert'),
                    LogLevel::CRITICAL => __('Critical'),
                    LogLevel::ERROR => __('Error'),
                    LogLevel::WARNING => __('Warning'),
                    LogLevel::NOTICE => __('Notice'),
                    LogLevel::INFO => __('Info'),
                    LogLevel::DEBUG => __('Debug'),
                ],
            ]
        );

        $this->addColumn(
            'message',
            [
                'header' => __('Message'),
                'index' => 'message',
            ]
        );

        $this->addColumn(
            'row_status',
            [
                'header' => __('Status'),
                'index' => 'row_status',
                'type' => 'options',
                'options' => [
                    LogStatus::SUCCESS => __('Success'),
                    LogStatus::FAILED => __('Failed'),
                    LogStatus::COMPLETED => __('Completed'),
                    LogStatus::INCOMPLETE => __('Incomplete'),
                    LogStatus::PROCESSED => __('Processed'),
                    LogStatus::PARTIALLYPROCESSED => __('Partially Processed'),
                    LogStatus::INPROGRESS => __('In Progress'),
                    LogStatus::NA => __('Status Not Available')
                ],
            ]
        );

        $this->addColumn(
            'log_id',
            [
                'header' => __('Log ID'),
                'index' => 'log_id',
            ]
        );

        $this->addColumn(
            'log_filename',
            [
                'header' => __('Log filename'),
                'index' => 'log_filename',
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');

        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @param DataObject $item
     * @param WriteInterface $stream
     * @throws FileSystemException
     */
    protected function exportJsonItem(DataObject $item, WriteInterface $stream)
    {
        static $isFirstRow = true;
        $data = $item->getData();
        if ($message = json_decode($data['message'], true)){
            $data['message'] = $message;
        }
        $stream->write($isFirstRow ? '' : ',');
        $isFirstRow = false;
        $stream->write(json_encode($data));
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
            'rm' => true  // can delete file after use
        ];
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('corra_log/*/index', ['_current' => true]);
    }
}
