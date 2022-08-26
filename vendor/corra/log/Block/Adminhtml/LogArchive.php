<?php

namespace Corra\Log\Block\Adminhtml;

use Corra\Log\Block\Adminhtml\LogArchive\Grid;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class LogArchive
 *
 * Corra\Log\Block\Adminhtml
 */
class LogArchive extends Container
{
    /**
     * @var string
     */
    protected $_template = 'log/log.phtml';

    /**
     * LogArchive constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid.
     *
     * @return Container
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(Grid::class, 'corra_log.log_archive.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     * Render grid.
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
