<?php

namespace Corra\Log\Model\ResourceModel;

use Corra\Log\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Corra\Log\Helper\Data;
use Magento\Framework\Escaper;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Area;
use Magento\Store\Model\Store;
use Magento\Framework\DataObject;

/**
 * Class AlertDigestEmailSender
 *
 * Corra\Log\Model\ResourceModel
 */
class AlertDigestEmailSender
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
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TimezoneInterface
     */
    protected $timeZoneInterface;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * AlertDigestEmailSender constructor.
     *
     * @param Config $config
     * @param DateTime $dateTime
     * @param ResourceConnection $resource
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param TimezoneInterface $timeZoneInterface
     * @param Escaper $escaper
     * @param State $state
     * @param Data $helperData
     */
    public function __construct(
        Config $config,
        DateTime $dateTime,
        ResourceConnection $resource,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        TimezoneInterface $timeZoneInterface,
        Escaper $escaper,
        State $state,
        Data $helperData
    ) {
        $this->helperData = $helperData;
        $this->timeZoneInterface = $timeZoneInterface;
        $this->config = $config;
        $this->dateTime = $dateTime;
        $this->resource = $resource;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->state = $state;
    }

    /**
     * Send alert email digest taking into consideration frequency setting for each log level.
     * @throws \Exception
     */
    public function processAlerts()
    {
        if (!$this->state->getAreaCode()) {
            $this->state->setAreaCode(Area::AREA_FRONTEND);
        }

        $connection = $this->resource->getConnection();
        $connection->beginTransaction();

        $select = $connection->select()
            ->from(
                ['alert' => $this->resource->getTableName('corra_log_alert')],
                ['MIN(created_at) AS time', 'level']
            )
            ->group('level');
        $dateByLevel = $connection->fetchAll($select);
        $alertMessages = [];
        $processedIds = [];

        foreach ($dateByLevel as $alertMessage) {
            $timeSent = new \DateTime($alertMessage['time']);
            $timeNow = new \DateTime();
            $timestamp = ($timeNow->getTimestamp() - $timeSent->getTimestamp());
            $minutesPassed = $timestamp / 60;
            $periodByLevel = $this->config->getPeriodByLevel($alertMessage['level']);

            if ($minutesPassed > $periodByLevel) {
                $select = $connection->select()
                    ->from(
                        [
                            'alert' => $this->resource->getTableName(
                                'corra_log_alert'
                            )
                        ],
                        [
                            'id',
                            'created_at',
                            'level',
                            'message',
                            'type',
                            'subtype',
                            'log_id'
                        ]
                    )
                    ->where('level = ?', $alertMessage['level']);

                $messagesByLevel = $connection->fetchAll($select);
                foreach ($messagesByLevel as $message) {
                    $alertMessages[] = $message;
                    $processedIds[] = $message['id'];
                }
            }
        }

        $messagesBody = '';
        $messagesStyles = 'class="tg-2ktp"'
            . 'style="font-size:16px;vertical-align:top;font-family:Arial, '
            . 'sans-serif;padding:10px 5px;border-style:solid;border-width:1px;'
            . 'overflow:hidden;word-break:normal"';

        foreach ($alertMessages as $message) {
            $messagesBody .= '<tr>';
            $messagesBody .= '<td ' . $messagesStyles . '>'
                . $message['id'] . '</td>';
            $messagesBody .= '<td ' . $messagesStyles . '>'
                . $this->helperData->convertTimeZone(
                    (string) $message['created_at'],
                    'UTC',
                    (string) $this->timeZoneInterface->getConfigTimezone()
                )
                . '</td>';
            $messagesBody .= '<td ' . $messagesStyles . '>'
                . ucfirst($message['level']) . '</td>';
            $messagesBody .= '<td ' . $messagesStyles . '>'
                . $message['message'] . '</td>';
            $messagesBody .= '<td ' . $messagesStyles . '>'
                . $message['log_id'] . '</td>';
            $messagesBody .= '</tr>';
        }

        $configTimezone = (string) $this->timeZoneInterface->getConfigTimezone();

        $post = [
            'alerts_messages' => $messagesBody,
            'alerts_subject'  => "Alerts",
            'local_timezone'  => $configTimezone
        ];

        $this->inlineTranslation->suspend();

        try {
            $postObject = new DataObject();
            $postObject->setData($post);

            $sender = [
                'name' => (string) $this->config->getNameFrom(),
                'email' => (string) $this->config->getEmailFrom(),
            ];

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->config->getAlertDigestEmailTemplate())
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($sender);

            foreach ($this->config->getEmailsTo() as $email) {
                $transport->addTo($email);
            }

            if (trim($messagesBody) != "") {
                $transport->getTransport()->sendMessage();
            }

            $connection->delete(
                'corra_log_alert',
                ['id IN (?)' => $processedIds]
            );

            $connection->commit();

        } catch (\Exception $e) {
            $connection->rollBack();
        }

        $this->inlineTranslation->resume();
    }

    /**
     * @return array
     */
    protected function getAllTypes()
    {
        $typeArray = [];
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                ['alert' => $this->resource->getTableName('corra_log_alert')],
                ['type']
            )
            ->group('type');
        $allTypes = $connection->fetchAll($select);

        foreach ($allTypes as $type) {
            $typeArray[] = $type['type'];
        }

        return $typeArray;
    }

    /**
     * @return array
     */
    protected function getAllSubTypes()
    {
        $subTypeArray  = [];
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                ['alert' => $this->resource->getTableName('corra_log_alert')],
                ['subtype']
            )
            ->group('subtype');
        $allSubTypes = $connection->fetchAll($select);

        foreach ($allSubTypes as $subType) {
            $subTypeArray[] = $subType['subtype'];
        }

        return $subTypeArray;
    }
}
