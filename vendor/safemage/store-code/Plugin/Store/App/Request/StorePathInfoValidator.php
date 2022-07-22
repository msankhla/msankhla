<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/

namespace SafeMage\StoreCode\Plugin\Store\App\Request;

class StorePathInfoValidator
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var \Magento\Framework\App\Request\PathInfo
     */
    private $pathInfo;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    ) {
        $this->objectManager = $objectManager;
        $this->productMetadata = $productMetadata;
        $this->config = $config;
        $this->storeRepository = $storeRepository;

        if (version_compare($this->productMetadata->getVersion(), '2.3.0') >= 0) {
            $this->pathInfo =
                $this->objectManager->create('Magento\Framework\App\Request\PathInfo');
        }
    }

    /**
     * @param \Magento\Store\App\Request\StorePathInfoValidator $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\Request\Http $request
     * @param string $pathInfo
     * @return null|string
     */
    public function aroundGetValidStoreCode(
        \Magento\Store\App\Request\StorePathInfoValidator $subject,
        \Closure $proceed,
        \Magento\Framework\App\Request\Http $request,
        $pathInfo = ''
    ) {
        if (empty($pathInfo)) {
            $pathInfo = $this->pathInfo->getPathInfo(
                $request->getRequestUri(),
                $request->getBaseUrl()
            );
        }

        $storeCode = $this->getStoreCode($pathInfo);

        if (!empty($storeCode)
            && $storeCode != \Magento\Store\Model\Store::ADMIN_CODE
            && (bool)$this->config->getValue(\Magento\Store\Model\Store::XML_PATH_STORE_IN_URL, 'store', $storeCode)
        ) {
            try {
                $this->storeRepository->getActiveStoreByCode($storeCode);

                if ((bool)$this->config->getValue(
                    \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeCode
                )) {
                    return $storeCode;
                }
            } catch (\Exception $e) {}
        }

        return null;
    }

    /**
     * @param $pathInfo
     * @return string
     */
    private function getStoreCode($pathInfo)
    {
        $pathParts = explode('/', ltrim($pathInfo, '/'), 2);
        return current($pathParts);
    }
}
