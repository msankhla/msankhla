<?php
/**
 * FedEx Cross Border component
 *
 * @category    FedEx
 * @package     FedEx_CrossBorder
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\CrossBorder\Model;

use FedEx\CrossBorder\Api\Data\SchedulerInterface;
use FedEx\CrossBorder\Model\ResourceModel\Scheduler as ResourceModel;
use FedEx\CrossBorder\Model\Scheduler\Sender;
use FedEx\CrossBorder\Model\Scheduler\SenderFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Scheduler extends AbstractModel implements SchedulerInterface
{
    /**
     * @var SenderFactory
     */
    protected $_senderFactory;

    /**
     * Scheduler constructor.
     *
     * @param SenderFactory $senderFactory
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        SenderFactory $senderFactory,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_senderFactory = $senderFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Returns type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getData(static::TYPE);
    }

    /**
     * Sets type
     *
     * @param string $value
     * @return $this
     */
    public function setType($value)
    {
        return $this->setData(static::TYPE, $value);
    }

    /**
     * Returns status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(static::STATUS);
    }

    /**
     * Sets status
     *
     * @param string $value
     * @return $this
     */
    public function setStatus($value)
    {
        return $this->setData(static::STATUS, $value);
    }

    /**
     * Returns json data
     *
     * @return string
     */
    public function getJsonData()
    {
        return $this->getData(static::JSON_DATA);
    }

    /**
     * Sets json data
     *
     * @param string $value
     * @return $this
     */
    public function setJsonData($value)
    {
        return $this->setData(static::JSON_DATA, $value);
    }

    /**
     * Returns number of attempts
     *
     * @return int
     */
    public function getAttempts()
    {
        return $this->getData(static::ATTEMPTS);
    }

    /**
     * Sets number of attempts
     *
     * @param int $value
     * @return $this
     */
    public function setAttempts($value)
    {
        return $this->setData(static::ATTEMPTS, $value);
    }

    /**
     * Returns created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(static::CREATED_AT);
    }

    /**
     * Sets created at
     *
     * @param string $value
     * @return $this
     */
    public function setCreatedAt($value)
    {
        return $this->setData(static::CREATED_AT, $value);
    }

    /**
     * Returns updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(static::UPDATED_AT);
    }

    /**
     * Sets updated at
     *
     * @param string $value
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        return $this->setData(static::UPDATED_AT, $value);
    }

    /**
     * Checks if scheduler can be run
     *
     * @return bool
     */
    public function canRun()
    {
        return in_array($this->getStatus(), [SchedulerInterface::STATUS_NEW, SchedulerInterface::STATUS_ERROR]);
    }

    /**
     * Run scheduler
     *
     * @param bool $reload
     * @return $this
     */
    public function run($reload = false)
    {
        if ($reload) {
            $this->load($this->getId());
        }

        if ($this->canRun()) {
            if ($this->getAttempts() < static::ATTEMPTS_LIMIT) {
                $this->setStatus(SchedulerInterface::STATUS_IN_PROGRESS)->save();
                /** @var Sender $sender */
                $sender = $this->_senderFactory->create();
                $sender->setType(
                    $this->getType()
                )->addHeader(
                    'Content-Type',
                    'application/json'
                )->addData(
                    $this->getJsonData()
                );

                $sender->getResponse(Sender::METHOD_POST);
                $this->setAttempts(
                    $this->getAttempts() + 1
                )->setStatus(
                    $sender->hasError() ?
                    (
                        $this->getAttempts() < SchedulerInterface::ATTEMPTS_LIMIT ?
                        SchedulerInterface::STATUS_ERROR :
                        SchedulerInterface::STATUS_CANCEL
                    ) :
                    SchedulerInterface::STATUS_SUCCESS
                )->save();
            } else {
                $this->setStatus(SchedulerInterface::STATUS_CANCEL)->save();
            }
        }

        return $this;
    }
}