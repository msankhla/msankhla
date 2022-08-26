<?php

namespace Corra\Log\Model;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\DeploymentConfig;

/**
 * Class Config
 *
 * Corra\Log\Model
 */
class Config
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param ReinitableConfigInterface $reinitableConfig
     * @param UrlInterface $urlInterface
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        ReinitableConfigInterface $reinitableConfig,
        DeploymentConfig $deploymentConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->reinitableConfig = $reinitableConfig;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return int
     */
    public function getCronDaysToArchive(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue(
            'corra_log/cron/days_to_archive',
            $scopeType,
            $scopeCode
        );
    }

    /**
     * @param string $scopeType
     * @param null $scopeCode
     * @return int
     */
    public function getCronDaysToWipe(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue(
            'corra_log/cron/days_to_wipe',
            $scopeType,
            $scopeCode
        );
    }

    /**
     * @param string $scopeType
     * @param null|string    $scopeCode
     * @return string
     */
    public function getEmailFrom(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $path =  (string)$this->scopeConfig->getValue(
            'corra_log/email_alerts/digest_email_from',
            $scopeType,
            $scopeCode
        );
        $email =  (string)$this->scopeConfig->getValue(
            'trans_email/ident_'.$path.'/email',
            $scopeType,
            $scopeCode
        );
        return $email;
    }

    /**
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function getNameFrom(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $path =  (string)$this->scopeConfig->getValue(
            'corra_log/email_alerts/digest_email_from',
            $scopeType,
            $scopeCode
        );
        $name =  (string)$this->scopeConfig->getValue(
            'trans_email/ident_'.$path.'/name',
            $scopeType,
            $scopeCode
        );
        return $name;
    }

    /**
     * @param string $scopeType
     * @param null|string    $scopeCode
     * @return string[]
     */
    public function getEmailsTo(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        return explode(
            ',',
            $this->scopeConfig->getValue(
                'corra_log/email_alerts/digest_emails_to',
                $scopeType,
                $scopeCode
            )
        );
    }

    /**
     * @param string $scopeType
     * @param null|string    $scopeCode
     * @return string
     */
    public function getAlertDigestEmailTemplate(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue(
            'corra_log/email_alerts/email_template',
            $scopeType,
            $scopeCode
        );
    }

    /**
     * @param string $level
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return int
     */
    public function getPeriodByLevel(
        $level,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        return $this->scopeConfig->getValue(
            'corra_log/email_alerts/' . $level . '_period',
            $scopeType,
            $scopeCode
        );
    }

    /**
     * @return bool
     */
    public function separateEmailsByTypeAndSubtype()
    {
        return $this->scopeConfig->isSetFlag(
            'corra_log/email_alerts/send_by_type_subtype'
        );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->deploymentConfig->get(
            'corra_log/general/enable_corra_logging',1
        );
    }

    /**
     * @return bool
     */
    public function isCorraLoggingEnabled()
    {
        return $this->deploymentConfig->get(
            'corra_log/graylog/enable_corra_logging',0
        );
    }

    /**
     * @return bool
     */
    public function isAllLoggingTemporaryEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'corra_log/graylog/enable_all_logging_temporary'
        );
    }

    /**
     * Disable all logging.
     */
    public function disableAllLoggingTemporaryEnabled()
    {
        $this->configWriter->save(
            'corra_log/graylog/enable_all_logging_temporary',
            0
        );
        $this->reinitableConfig->reinit();
    }

    /**
     * @return int
     */
    public function getAllLoggingEnabledTill()
    {
        return $this->scopeConfig->getValue(
            'corra_log/graylog/enable_all_logging_till'
        );
    }

    /**
     * Set all logging enabled till time.
     */
    public function setAllLoggingEnabledTill()
    {
        $timePeriod = (new \DateTime())->getTimestamp()
            + $this->getAllLoggingTemporaryPeriod() * 60;
        $this->configWriter->save(
            'corra_log/graylog/enable_all_logging_till',
            $timePeriod
        );
        $this->reinitableConfig->reinit();
    }

    /**
     * @return int
     */
    public function getAllLoggingTemporaryPeriod()
    {
        return $this->scopeConfig->getValue(
            'corra_log/graylog/enable_all_logging_temporary_period'
        );
    }

    /**
     * @return string
     */
    public function getGraylogHost()
    {
        return $this->scopeConfig->getValue('corra_log/graylog/graylog_host');
    }

    /**
     * @return int
     */
    public function getGraylogPort()
    {
        return $this->scopeConfig->getValue('corra_log/graylog/graylog_port');
    }

    /**
     * @return mixed
     */
    public function getLogLevelThreshold()
    {
        return $this->scopeConfig->getValue('corra_log/graylog/log_level_threshold');
    }
}
