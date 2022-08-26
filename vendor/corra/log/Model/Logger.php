<?php
/**
 * @author CORRA
 */
namespace Corra\Log\Model;

use Corra\Log\Helper\Data;
use Monolog\DateTimeImmutable;
use Psr\Log\LogLevel;
use Magento\Framework\Logger\Monolog;

/**
 * Class Logger
 *
 * @package Corra\Log\Model
 */
class Logger extends Monolog
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Logger constructor.
     *
     * @param Data $helper
     * @param $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        Data $helper,
        string $name,
        array $handlers = [],
        array $processors = []
    ) {
        $this->helper = $helper;
        $handlers = array_values($handlers);
        parent::__construct($name, $handlers, $processors);
    }


    /**
     * {@inheritdoc}
     */
    public function addRecord(int $level, string $message, array $context = [], DateTimeImmutable $datetime = null): bool
    {
        $levelName = (int) $level ? $this->getLevelNameByCode($level) : $level;
        $messageProcessed = $this->helper->processMessage(
            $levelName,
            $message,
            $context
        );

        if (!$messageProcessed) {
            $messageProcessed = parent::addRecord($level, $message, $context);
        }

        return $messageProcessed;
    }

    /**
     * Get level name by messege code.
     *
     * @param int $code
     * @return string
     */
    private function getLevelNameByCode($code)
    {
        try {
            $level = Logger::getLevelName($code);
        } catch (\InvalidArgumentException $exception) {
            $level = Logger::getLevelName(Logger::CRITICAL);
        }

        return $level;
    }
}
