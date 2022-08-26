<?php

namespace Corra\Log\Model;

use Corra\Log\Model\ResourceModel\Alert as AlertResource;
use Corra\Log\Model\ResourceModel\Log as LogResource;
use Magento\Framework\FilesystemFactory;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\DB\Adapter\Pdo\Mysql;

/**
 * Class LogCreatedConsumer.
 *
 * Corra\Log\Model
 */
class LogCreatedConsumer
{
    /**
     * @var string
     */
    const SEVERITY = 'Severity:';

    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * @var LogResource
     */
    private $logResource;

    /**
     * @var AlertFactory
     */
    private $alertFactory;

    /**
     * @var AlertResource
     */
    private $alertResource;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var GraylogHandler
     */
    private $graylogHandler;

    /**
     * @var FilesystemFactory
     */
    private $filesystemFactory;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * LogCreatedConsumer constructor.
     *
     * @param LogFactory $logFactory
     * @param LogResource $logResource
     * @param AlertFactory $alertFactory
     * @param AlertResource $alertResource
     * @param Config $config
     * @param GraylogHandler $graylogHandler
     * @param FilesystemFactory $filesystemFactory
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        LogFactory $logFactory,
        LogResource $logResource,
        AlertFactory $alertFactory,
        AlertResource $alertResource,
        Config $config,
        GraylogHandler $graylogHandler,
        FilesystemFactory $filesystemFactory,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->logFactory = $logFactory;
        $this->logResource = $logResource;
        $this->alertFactory = $alertFactory;
        $this->alertResource = $alertResource;
        $this->config = $config;
        $this->graylogHandler = $graylogHandler;
        $this->filesystemFactory = $filesystemFactory;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @param string $data
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function process($data)
    {
        list($level, $message, $context) = json_decode($data, true);
        $date = $this->dateTimeFactory->create();
        $level = strtolower($level);
        $currentDateTime = $date->format(Mysql::TIMESTAMP_FORMAT);
        $datetime = isset($context['datetime'])
            ? $context['datetime']
            : $currentDateTime;
        $logModel = $this->logFactory->create();
        $logModel->setLevel($level);
        $message = json_decode($message, true);
        $logModel->setMessage(json_encode($message['message']));

        $messageStatus = isset($message['status']) ? $message['status'] : '';
        if ($messageStatus == LogStatus::SUCCESS
            || $messageStatus == LogStatus::FAILED
            || $messageStatus == LogStatus::COMPLETED
            || $messageStatus == LogStatus::INCOMPLETE
            || $messageStatus == LogStatus::PROCESSED
            || $messageStatus == LogStatus::PARTIALLYPROCESSED
            || $messageStatus == LogStatus::INPROGRESS
        ) {
            $logModel->setRowStatus($messageStatus);
        } else {
            $logModel->setRowStatus(LogStatus::NA);
        }

        if (isset($message['alert'])) {
            $alertModel = $this->alertFactory->create();
            $alertModel->setLevel($level);
            $alertModel->setMessage(json_encode($message['alert']));

            if (isset($context['logNalert']['message']['type'])) {
                $alertModel->setType($context['logNalert']['message']['type']);
            }

            if (isset($context['logNalert']['message']['subtype'])) {
                $alertModel->setSubtype($context['logNalert']['message']['subtype']);
            }

            if (isset($context['logNalert']['unique_id'])) {
                $alertModel->setLogId($context['logNalert']['unique_id']);
            } else {
                $alertModel->setLogId('Not Available');
            }

            $alertModel->setCreatedAt($datetime);
            $this->alertResource->save($alertModel);
        }

        if (isset($context['logNalert'])) {
            if (isset($context['logNalert']['unique_id'])) {
                $logModel->setLogId($context['logNalert']['unique_id']);
            }

            if (isset($context['logNalert']['message']['type'])) {
                $logModel->setType($context['logNalert']['message']['type']);
            }

            if (isset($context['logNalert']['message']['subtype'])) {
                $logModel->setSubtype($context['logNalert']['message']['subtype']);
            }

            if (isset($context['logNalert']['trace']['filename'])) {
                $logModel->setLogFilename(
                    $context['logNalert']['trace']['filename']
                );
            }

            $logModel->setCreatedAt($datetime);
        }

        $this->logResource->save($logModel);

        if (isset($message['trace'])
            && isset($context['logNalert']['trace']['filename'])
        ) {
            $filesystem = $this->filesystemFactory->create();
            $writer = $filesystem->getDirectoryWrite('log');
            $file = $writer->openFile(
                $context['logNalert']['trace']['filename'],
                'a'
            );

            $data = 'TRACE: ' . json_encode($message['trace']);

            if (isset($context['logNalert']['unique_id'])) {
                $data = 'UNIQID: '
                    . $context['logNalert']['unique_id']
                    . ' '
                    . $data;
            }

            $data = '[' . $datetime . ']'
            . ' '
            . self::SEVERITY
            . ' '
            . strtoupper($level)
            . ' '
            . $data;

            $file->write($data . PHP_EOL);
        }

        if ($this->config->isCorraLoggingEnabled()) {
            $this->graylogHandler->write(
                [
                    'datetime' => $this->dateTimeFactory->create(),
                    'level' => $level,
                    'message' => $message,
                    'context' => $context,
                    'extra' => [],
                ]
            );
        }
    }
}
