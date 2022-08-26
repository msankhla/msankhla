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

use FedEx\Core\Model\Log;
use FedEx\CrossBorder\Api\Data\AvailableCountriesInterface;
use FedEx\CrossBorder\Helper\Data as Helper;
use FedEx\CrossBorder\Model\ResourceModel\AvailableCountries as ResourceModel;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class AvailableCountries extends AbstractModel implements
    IdentityInterface,
    AvailableCountriesInterface
{
    const CACHE_TAG                     = 'fedex_crossborder_availablecountries';

    const LOG_FILE                      = 'FedEx/CrossBorder/AvailableCountries.log';
    const ERROR_LOG                     = '[%s] Error [%s]: %s';
    const MSG_RECEIVED                  = '[%s] Received data: %s';
    const TIMEOUT                       = 100;

    /**
     * @var string
     */
    protected $_cacheTag                = 'fedex_crossborder_availablecountries';

    /**
     * @var string
     */
    protected $_eventPrefix             = 'fedex_crossborder_availablecountries';

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * AvailableCountries constructor.
     *
     * @param Helper $helper
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Helper $helper,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_helper = $helper;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Adds log
     *
     * @param mixed $message
     * @return $this
     */
    public function addLog($message)
    {
        if ($this->_helper->isLogsEnabled()) {
            Log::Info($message, static::LOG_FILE);
        }

        return $this;
    }

    /**
     * Returns country code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * Sets country code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * Returns country default currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->getData(self::CURRENCY);
    }

    /**
     * Sets country default currency
     *
     * @param string $code
     * @return $this
     */
    public function setCurrency($code)
    {
        return $this->setData(self::CURRENCY, $code);
    }

    /**
     * Returns country name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Sets country name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}