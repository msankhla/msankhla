<?php

namespace Corra\Log\Model\ResourceModel;

use Corra\Log\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class LogArchiveRefresher
 *
 * Corra\Log\Model\ResourceModel
 */
class LogArchiveRefresher
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var Config
     */
    private $config;

    /**
     * LogArchiveRefresher constructor.
     * @param Config $config
     * @param DateTime $dateTime
     * @param ResourceConnection $resource
     */
    public function __construct(
        Config $config,
        DateTime $dateTime,
        ResourceConnection $resource
    ) {
        $this->config = $config;
        $this->dateTime = $dateTime;
        $this->resource = $resource;
    }

    /**
     * Move data more than <days_to_archive> old from corra_log to corra_log_archive
     * Delete data older than <days_to_wipe> old from corra_log_archive table
     */
    public function refresh()
    {
        $dateToArchive = $this->dateTime->formatDate(
            '-' . $this->config->getCronDaysToArchive() . ' days'
        );
        $dateToWipe = $this->dateTime->formatDate(
            '-' . $this->config->getCronDaysToWipe() . ' days'
        );

        $connection = $this->resource->getConnection();
        $connection->beginTransaction();

        $select = $connection->select()
            ->from(['log' => $this->resource->getTableName('corra_log')])
            ->where('log.created_at < ?', $dateToArchive);

        $connection->query(
            $select->insertFromSelect(
                $connection->getTableName('corra_log_archive')
            )
        );

        $connection->delete('corra_log', ['created_at < ?' => $dateToArchive]);
        $connection->delete('corra_log_archive', ['created_at < ?' => $dateToWipe]);

        $connection->commit();
    }
}
