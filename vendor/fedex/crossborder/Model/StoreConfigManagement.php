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

use FedEx\CrossBorder\Api\Data\StoreConfigInterface;
use FedEx\CrossBorder\Api\Data\StoreConfigInterfaceFactory;
use FedEx\CrossBorder\Api\StoreConfigManagementInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class StoreConfigManagement implements StoreConfigManagementInterface
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Map the setters to config path
     *
     * @var array
     */
    protected $_configPaths = [
        'setLocale'                     => 'general/locale/code',
        'setBaseCurrencyCode'           => 'currency/options/base',
        'setDefaultDisplayCurrencyCode' => 'currency/options/default',
        'setTimezone'                   => 'general/locale/timezone',
        'setWeightUnit'                 => DirectoryHelper::XML_PATH_WEIGHT_UNIT,
        'setDimensionUnit'              => 'general/locale/dimension_unit',
        'setProductUrlSuffix'           => 'catalog/seo/product_url_suffix',
        'setCategoryUrlSuffix'          => 'catalog/seo/category_url_suffix',
        'setProductIdentifier'          => 'fedex_crossborder/import_export/identifier',
    ];

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var StoreConfigInterfaceFactory
     */
    protected $_storeConfigFactory;

    /**
     * StoreConfigManagement constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreConfigInterfaceFactory $storeConfigFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeConfig,
        StoreConfigInterfaceFactory $storeConfigFactory
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeConfigFactory = $storeConfigFactory;
    }

    /**
     * Returns store configs
     *
     * @param string[] $storeCodes
     * @return StoreConfigInterface[]
     */
    public function getStoreConfigs(array $storeCodes = null)
    {
        $storeConfigs = [];
        $collection = $this->_collectionFactory->create();
        if ($storeCodes != null) {
            $collection->addFieldToFilter('code', ['in' => $storeCodes]);
        }

        foreach ($collection as $item) {
            $storeConfigs[] = $this->getStoreConfig($item);
        }

        return $storeConfigs;
    }

    /**
     * Returns store config
     *
     * @param Store $store
     * @return StoreConfigInterface
     */
    public function getStoreConfig(Store $store)
    {
        /** @var StoreConfigInterface $storeConfig */
        $storeConfig = $this->_storeConfigFactory->create();

        $storeConfig->setId($store->getId())
            ->setCode($store->getCode())
            ->setWebsiteId($store->getWebsiteId());

        foreach ($this->_configPaths as $methodName => $configPath) {
            $configValue = $this->_scopeConfig->getValue(
                $configPath,
                ScopeInterface::SCOPE_STORES,
                $store->getCode()
            );
            $storeConfig->$methodName($configValue);
        }

        $storeConfig->setBaseUrl($store->getBaseUrl(UrlInterface::URL_TYPE_WEB, false));
        $storeConfig->setSecureBaseUrl($store->getBaseUrl(UrlInterface::URL_TYPE_WEB, true));
        $storeConfig->setBaseLinkUrl($store->getBaseUrl(UrlInterface::URL_TYPE_LINK, false));
        $storeConfig->setSecureBaseLinkUrl($store->getBaseUrl(UrlInterface::URL_TYPE_LINK, true));
        $storeConfig->setBaseStaticUrl($store->getBaseUrl(UrlInterface::URL_TYPE_STATIC, false));
        $storeConfig->setSecureBaseStaticUrl($store->getBaseUrl(UrlInterface::URL_TYPE_STATIC, true));
        $storeConfig->setBaseMediaUrl($store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA, false));
        $storeConfig->setSecureBaseMediaUrl($store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA, true));

        return $storeConfig;
    }
}