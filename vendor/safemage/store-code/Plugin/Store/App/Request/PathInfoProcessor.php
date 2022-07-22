<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/

namespace SafeMage\StoreCode\Plugin\Store\App\Request;

class PathInfoProcessor
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Store\App\Request\StorePathInfoValidator
     */
    private $storePathInfoValidator;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    private $config;

    /**
     * PathInfoProcessor constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $config
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config
    ) {
        $this->objectManager = $objectManager;
        $this->productMetadata = $productMetadata;
        if (version_compare($this->productMetadata->getVersion(), '2.3.0') >= 0) {
            $this->storePathInfoValidator =
                $this->objectManager->create('Magento\Store\App\Request\StorePathInfoValidator');
        }
        $this->config = $config;
    }

    /**
     * @param \Magento\Store\App\Request\PathInfoProcessor $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @param $pathInfo
     * @return string
     */
    public function aroundProcess(
        \Magento\Store\App\Request\PathInfoProcessor $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request,
        $pathInfo
    ) {
        if (version_compare($this->productMetadata->getVersion(), '2.3.0') >= 0) {
            //can store code be used in url
            $storeCode = $this->storePathInfoValidator->getValidStoreCode($request, $pathInfo);
            if ((bool)$this->config->getValue(\Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,  'store', $storeCode)) {
                if (!empty($storeCode)) {
                    if (!$request->isDirectAccessFrontendName($storeCode)) {
                        $pathInfo = $this->trimStoreCodeFromPathInfo($pathInfo, $storeCode);
                    } else {
                        //no route in case we're trying to access a store that has the same code as a direct access
                        $request->setActionName(\Magento\Framework\App\Router\Base::NO_ROUTE);
                    }
                }
            }
            return $pathInfo;
        } else {
            return $proceed($request, $pathInfo);
        }
    }

    /**
     * @param string $pathInfo
     * @param string $storeCode
     * @return string
     */
    private function trimStoreCodeFromPathInfo($pathInfo, $storeCode)
    {
        if (substr($pathInfo, 0, strlen('/' . $storeCode)) == '/'. $storeCode) {
            $pathInfo = substr($pathInfo, strlen($storeCode)+1);
        }
        return empty($pathInfo) ? '/' : $pathInfo;
    }
}
