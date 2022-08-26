<?php
/**
 * @package     BlueAcorn/Cpre
 * @version     1.0.0
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
namespace BlueAcorn\Core\Setup;

use BlueAcorn\Core\Model\ContentVersion\Action\ProcessContent;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * Class RecurringData
 *
 * @package BlueAcorn\Core\Setup
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @var ProcessContent
     */
    private $processContent;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProcessContent $processContent
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProcessContent $processContent,
        LoggerInterface $logger
    ) {
        $this->processContent = $processContent;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $this->processContent->execute();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
