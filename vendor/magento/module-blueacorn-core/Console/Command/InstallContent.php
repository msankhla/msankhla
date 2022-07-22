<?php
/**
 * @package     BlueAcorn/Core
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
declare(strict_types=1);

namespace BlueAcorn\Core\Console\Command;

use BlueAcorn\Core\Model\ContentVersion\Action\ProcessContent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallContent extends Command
{
    /**
     * @var ProcessContent
     */
    private $processContent;

    /**
     * @param ProcessContent $processContent
     * @param string|null $name
     */
    public function __construct(
        ProcessContent $processContent,
        string $name = null
    ) {
        $this->processContent = $processContent;
        parent::__construct($name);
    }

    /**
     * Configure command name and description
     */
    protected function configure()
    {
        $this->setName('blueacorn:core:installContent')
            ->setDescription('Triggers content version install process');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->processContent->execute();
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
        }
    }
}
