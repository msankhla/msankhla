<?php

namespace Corra\Log\Model;

use Gelf\PublisherFactory;
use Gelf\Transport\UdpTransportFactory;
use Monolog\Handler\GelfHandlerFactory;
use Gelf\Encoder\JsonEncoder;

/**
 * Class GraylogHandler
 *
 * Corra\Log\Model
 */
class GraylogHandler
{
    /**
     * @var UdpTransportFactory
     */
    private $udpTransportFactory;

    /**
     * @var PublisherFactory
     */
    private $publisherFactory;

    /**
     * @var GelfHandlerFactory
     */
    private $gelfHandler;

    /**
     * @var JsonEncoder
     */
    private $jsonEncoder;

    /**
     * @var Config
     */
    private $config;

    /**
     * GraylogHandler constructor.
     *
     * @param UdpTransportFactory $udpTransportFactory
     * @param PublisherFactory $publisherFactory
     * @param GelfHandlerFactory $gelfHandler
     * @param JsonEncoder $jsonEncoder
     * @param Config $config
     */
    public function __construct(
        UdpTransportFactory $udpTransportFactory,
        PublisherFactory $publisherFactory,
        GelfHandlerFactory $gelfHandler,
        JsonEncoder $jsonEncoder,
        Config $config
    ) {
        $this->gelfHandler = $gelfHandler;
        $this->jsonEncoder = $jsonEncoder;
        $this->config = $config;
        $this->udpTransportFactory = $udpTransportFactory;
        $this->publisherFactory = $publisherFactory;
    }

    /**
     * @param array $record
     */
    public function write(array $record)
    {
        $udpTransport = $this->udpTransportFactory->create(
            [
                'host' => $this->config->getGraylogHost(),
                'port' => $this->config->getGraylogPort()
            ]
        );
        $udpTransport->setMessageEncoder($this->jsonEncoder);
        $publisher = $this->publisherFactory->create(['transport' => $udpTransport]);
        $handler = $this->gelfHandler->create(
            [
                'publisher' => $publisher,
                'level' => $this->config->getLogLevelThreshold()
            ]
        );
        $handler->handle($record);
    }
}
