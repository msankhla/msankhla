<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/


namespace SafeMage\StoreCode\Plugin\Framework\App;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Store\Model\Store;

/**
 * Redirect to default store if store code is present in URL but switched off in settings.
 * For example: http://example.com/default will be redirected to http://example.com
 */
class RouterPlugin
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $response;

    /**
     * @var StoreCookieManagerInterface
     */
    private $storeCookieManager;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var int
     */
    private $redirectCode = 301;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Response\Http $response
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param HttpContext $httpContext
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\App\ActionFactory  $actionFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Response\Http $response,
        StoreCookieManagerInterface $storeCookieManager,
        HttpContext $httpContext,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\App\ActionFactory  $actionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->response = $response;
        $this->storeCookieManager = $storeCookieManager;
        $this->httpContext = $httpContext;
        $this->urlInterface = $urlInterface;
        $this->actionFactory = $actionFactory;
    }

    /**
     * Redirect to default store if store code is present in URL but switched off in settings.
     *
     * @param \Magento\Framework\App\RouterInterface $subject
     * @param string $result
     * @return \Magento\Framework\App\ActionInterface|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterMatch(\Magento\Framework\App\RouterInterface $subject, $result)
    {
        $defaultStoreView = $this->storeManager->getDefaultStoreView();
        $currentStore = $this->storeManager->getStore();
        if (is_null($result)) {
            $pathParts = explode('/', ltrim($this->request->getPathInfo(), '/'), 2);
            $storeCode = $pathParts[0];
            if ($storeCode) {
                try {
                    /** @var \Magento\Store\Api\Data\StoreInterface $store */
                    $store = $this->storeManager->getStore($storeCode);
                } catch (NoSuchEntityException $e) {
                    return $result;
                }
                if (!$store->isUseStoreInUrl()) {
                    if ($defaultStoreView->getId() == $store->getId()) {
                        $this->storeCookieManager->deleteStoreCookie($store);
                    } else {
                        $this->httpContext->setValue(Store::ENTITY, $store->getCode(), $defaultStoreView->getCode());
                        $this->storeCookieManager->setStoreCookie($store);
                    }
                    $pathInfo = (isset($pathParts[1]) ? $pathParts[1] : '');
                    $this->response->setRedirect($store->getBaseUrl() . $pathInfo, $this->redirectCode);
                    return $this->actionFactory->create(\Magento\Framework\App\Action\Redirect::class);
                }
            }
        }

        if (!is_null($result) && $this->validateStoreCodeInUrl($currentStore->getCode())
            && $currentStore->isUseStoreInUrl()) {
            $pathInfo = ($this->request->getOriginalPathInfo())
                ? ltrim($this->request->getOriginalPathInfo(), '/') : '';
            $this->response->setRedirect($currentStore->getBaseUrl() . $pathInfo, $this->redirectCode);
            return $result;
        }

        return $result;
    }

    /**
     * @param string $storeCode
     * @return bool
     */
    private function validateStoreCodeInUrl($storeCode)
    {
        $currentUrl = $this->urlInterface->getCurrentUrl();
        $requestPath = $this->request->getOriginalPathInfo();
        if (strpos(str_replace($requestPath, '', $currentUrl), $storeCode) === false) {
            return true;
        } else {
            return false;
        }
    }
}
