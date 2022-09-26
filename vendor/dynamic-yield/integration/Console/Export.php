<?php

namespace DynamicYield\Integration\Console;

use DynamicYield\Integration\Model\Export as ExportModel;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Export extends Command
{
    /**
     * @var ExportModel
     */
    protected $_export;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Export constructor.
     * @param ExportModel $export
     */
    public function __construct(
        ExportModel $export,
        State $state,
        LoggerInterface $logger,
        $name = null
    )
    {
        parent::__construct($name);

        $this->_export = $export;
        $this->_state = $state;
        $this->_logger = $logger;
    }

    /**
     * Set unlimited on console
     */
    protected function setUnlimited()
    {
        set_time_limit(0);
    }

    /**
     * Configure console
     */
    public function configure()
    {
        $this->setName('product:export')
            ->setDescription('Export Products');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return mixed
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUnlimited();

        try {
            $this->_state->setAreaCode('adminhtml');
        } catch (LocalizedException $e) {}

        try {
            $this->_export->export();
        } catch (\Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }
    }
}