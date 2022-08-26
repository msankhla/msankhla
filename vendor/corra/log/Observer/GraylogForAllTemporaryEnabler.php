<?php

namespace Corra\Log\Observer;

use Corra\Log\Model\Config;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class GraylogForAllTemporaryEnabler
 *
 * Corra\Log\Observer
 */
class GraylogForAllTemporaryEnabler implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * GraylogForAllTemporaryEnabler constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if ($this->config->isAllLoggingTemporaryEnabled()) {
            $this->config->disableAllLoggingTemporaryEnabled();
            $this->config->setAllLoggingEnabledTill();
        }
    }
}
