<?php

namespace Corra\Log\Helper;

use Corra\Log\Model\Config;
use Corra\Log\Model\GraylogHandler;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Class Data
 *
 * Corra\Log\Helper
 */
class Data
{
    /**
     * @var string
     */
    const TOPIC_NAME = 'corra.log.created';

    /**
     * @var GraylogHandler
     */
    private $graylogHandler;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * Data constructor.
     *
     * @param GraylogHandler $graylogHandler
     * @param Config $config
     * @param PublisherInterface $publisher
     */
    public function __construct(
        GraylogHandler $graylogHandler,
        Config $config,
        PublisherInterface $publisher
    ) {
        $this->graylogHandler = $graylogHandler;
        $this->config = $config;
        $this->publisher = $publisher;
    }

    /**
     * @param $level
     * @param $message
     * @param array $context
     * @return bool
     * @throws \Exception
     */
    public function processMessage($level, $message, $context = [])
    {
        if ($this->config->isEnabled() != 1) {
            return false;
        }
        $timezone = new \DateTimeZone("UTC");
        $now = new \DateTime("now", $timezone);
        $context['datetime'] = date("Y-m-d H:i:s");
        $decodedMessage = $this->serializeAndDecodeJsonToAray($message);
        /** not Corra_Log format, fallback to core logger */
        if (!is_array($decodedMessage) || (is_array($decodedMessage) && !isset($decodedMessage['message']))) {
            return false;
        }
        $arrayVal = json_encode([$level, json_encode($decodedMessage), $context]);
        $this->publisher->publish(self::TOPIC_NAME, $arrayVal);
        if (isset($decodedMessage['trace']) && !isset($context['logNalert']['trace']['filename'])) {
            return false;
        }
        return true;
    }

    /**
     * Replace sensitive data
     * 1) Credit card number (16 digits with space/hyphen as delimiter)
     * 4111 1111-1111 1111 -> XXXX XXXX XXXX 1111
     * 2) Email
     * some-email@example.com -> sXXXXl@example.com
     *
     * @param string $item
     */
    protected function anonymizeData($item)
    {
        $itemWithoutSpacesBetweenDigits = $item;
        $itemWithoutSpacesBetweenDigits = preg_replace(
            '/(\d)[\s-]+(\d)/',
            '$1$2',
            $itemWithoutSpacesBetweenDigits
        );
        $itemWithoutSpacesBetweenDigitsMatched = preg_match(
            '/([\D]|^)[\d]{16}([\D]|$)/',
            $itemWithoutSpacesBetweenDigits
        );

        if ($itemWithoutSpacesBetweenDigitsMatched) {
            $item = preg_replace(
                '/([\D]|^)[\d]{12}([\d]{4})([\D]|$)/',
                '$1XXXX XXXX XXXX $2$3',
                $itemWithoutSpacesBetweenDigits
            );
        }

        if (preg_match('/[\S]+@[\S]+/', $item)) {
            $item = preg_replace(
                '/([\S])[\S]*([\S]@[\S]+)/',
                '$1XXXX$2',
                $item
            );
        }

        return $item;
    }

    /**
     * @param string $time format '2008-08-03 12:35:23'
     * @param string $from format 'America/Los_Angeles', 'UTC'
     * @param string $to   format 'America/Los_Angeles', 'UTC'
     * @return string
     * @throws \Exception
     */
    public function convertTimeZone($time, $from, $to)
    {
        $fromTimeZone = new \DateTimeZone($from);
        $datetime = new \DateTime($time, $fromTimeZone);
        $toTimeZone = new \DateTimeZone($to);
        $datetime->setTimezone($toTimeZone);

        return $datetime->format('Y-m-d H:i:s');
    }

    /**
     * @param string $string
     * @return string|array
     */
    protected function serializeAndDecodeJsonToAray($string) {
        if(is_array($string)) {
            $array =  $string;
        } else {
            $array =  json_decode($string, true);
        }
        if (is_array($array)) {
            $result = [];
            foreach ($array as $key => $value) {
                $result[$key] = $this->serializeAndDecodeJsonToAray($value);
            }
            return $result;
        }
        return $this->anonymizeData($string);
    }
}
