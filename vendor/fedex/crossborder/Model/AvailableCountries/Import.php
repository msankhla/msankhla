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
namespace FedEx\CrossBorder\Model\AvailableCountries;

use FedEx\CrossBorder\Model\AbstractImport;
use FedEx\CrossBorder\Model\ResourceModel\AvailableCountries as ResourceModel;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\HTTP\ZendClientFactory;

class Import extends AbstractImport
{
    const ENDPOINT_PATH = 'available_countries_path';
    const TYPE_SHIPPING = 'shipping';
    const TYPE_BILLING  = 'billing';

    /**
     * @var ResourceModel
     */
    protected $_resource;

    /**
     * @var string
     */
    protected $_type    = self::TYPE_SHIPPING;

    /**
     * Import constructor.
     *
     * @param ResourceModel $resource
     * @param ScopeConfig $scopeConfig
     * @param ZendClientFactory $httpClientFactory
     */
    public function __construct(
        ResourceModel $resource,
        ScopeConfig $scopeConfig,
        ZendClientFactory $httpClientFactory
    ) {
        $this->_resource = $resource;

        parent::__construct($scopeConfig, $httpClientFactory);
    }

    /**
     * Returns type of data
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Returns api url parameters
     *
     * @return string
     */
    public function getUrlParams()
    {
        return '?type=' . $this->getType();
    }

    /**
     * Sets type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->_type = ($type == self::TYPE_BILLING ? $type : self::TYPE_SHIPPING);

        return $this;
    }

    /**
     * Add item into table
     *
     * @param array $data
     * @return $this
     */
    public function addItem($data)
    {
        $this->_resource->getConnection()->insertOnDuplicate(
            $this->_resource->getMainTable(),
            $this->convertData($data)
        );

        return $this;
    }

    /**
     * Converting data
     *
     * @param array $data
     * @return array
     */
    public function convertData($data)
    {
        return [
            'code'      => (isset($data['country_code']) ? $data['country_code'] : ''),
            'name'      => (isset($data['country_name']) ? $data['country_name'] : ''),
            'currency'  => (isset($data['currency']) ? $data['currency'] : ''),
        ];
    }

    /**
     * Execute import process
     *
     * @return $this
     */
    public function execute()
    {
        try {
            foreach ($this->getResponse() as $item) {
                $this->addItem($item);
            }
        } catch (\Exception $exception) {
            $this->_hasError = true;
            $this->_errorMessage = $exception->getMessage();
            $this->addLog(sprintf(
                self::ERROR,
                $exception->getCode(),
                $exception->getMessage()
            ));
        }

        return $this;
    }
}