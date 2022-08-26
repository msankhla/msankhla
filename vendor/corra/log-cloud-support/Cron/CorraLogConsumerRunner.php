<?php

namespace Corra\LogCloudSupport\Cron;

use Magento\Framework\MessageQueue\QueueRepository;
use Magento\MessageQueue\Model\Cron\ConsumersRunner;
use Magento\Framework\ShellInterface;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Symfony\Component\Process\PhpExecutableFinder;
use Magento\Framework\Lock\LockManagerInterface;

class CorraLogConsumerRunner extends ConsumersRunner
{

    /**
     * Shell command line wrapper for executing command in background
     *
     * @var ShellInterface
     */
    private $shellBackground;

    /**
     * Consumer config provider
     *
     * @var ConsumerConfigInterface
     */
    private $consumerConfig;

    /**
     * Application deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * The executable finder specifically designed for the PHP executable
     *
     * @var PhpExecutableFinder
     */
    private $phpExecutableFinder;

    /**
     * Lock Manager
     *
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @param PhpExecutableFinder $phpExecutableFinder The executable finder specifically designed
     *        for the PHP executable
     * @param ConsumerConfigInterface $consumerConfig The consumer config provider
     * @param DeploymentConfig $deploymentConfig The application deployment configuration
     * @param ShellInterface $shellBackground The shell command line wrapper for executing command in background
     * @param PidConsumerManager $pidConsumerManager The class for checking status of process by PID
     * @param QueueRepository $queueRepository
     */
    public function __construct(
        PhpExecutableFinder $phpExecutableFinder,
        ConsumerConfigInterface $consumerConfig,
        DeploymentConfig $deploymentConfig,
        ShellInterface $shellBackground,
        LockManagerInterface $lockManager,
        QueueRepository $queueRepository
    ) {
        $this->phpExecutableFinder = $phpExecutableFinder;
        $this->consumerConfig = $consumerConfig;
        $this->deploymentConfig = $deploymentConfig;
        $this->shellBackground = $shellBackground;
        $this->lockManager = $lockManager;
        $this->queueRepository = $queueRepository;
    }

    /**
     * Runs consumers processes
     */
    public function run(): void
    {
        // getting message count
        $consumerName = 'corraLogCreated';
        $queueName = 'corra_log_created';
        $connectionName = 'amqp';
        if (!$this->canBeRun($consumerName)) {
            return;
        }
        $queue = $this->queueRepository->get($connectionName, $queueName);
        $messages = [];
        while ($message = $queue->dequeue()) {
            $messages[] = $message;
        }
        $totalMessages = count($messages);
        if ($totalMessages <= 0) {
            return;
        }

        $maxMessages = (int) $totalMessages;
        $php = $this->phpExecutableFinder->find() ?: 'php';

        $arguments = [
            $consumerName,
            '--single-thread'
        ];

        if ($maxMessages) {
            $arguments[] = '--max-messages=' . $maxMessages;
        }

        $command = $php . ' ' . BP . '/bin/magento queue:consumers:start %s %s'
            . ($maxMessages ? ' %s' : '');

        $this->shellBackground->execute($command, $arguments);
    }

    /**
     * Checks that the consumer can be run
     *
     * @param string $consumerName The consumer name
     * @return bool Returns true if the consumer can be run
     */
    private function canBeRun($consumerName)
    {
        return !$this->lockManager->isLocked(md5($consumerName));
    }
}
