<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/

namespace SafeMage\StoreCode\Plugin\Store\Controller\Store;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreResolver;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Replaces execute method in Magento\Store\Controller\Store\SwitchAction plugin to make store switcher in header work
 * when switching between websites.
 */
class SwitchActionPlugin
{
    /**
     * @var StoreCookieManagerInterface
     */
    private $storeCookieManager;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * @var \SafeMage\StoreCode\Model\ValidateStore
     */
    private $storeValidator;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * SwitchActionPlugin constructor.
     *
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param HttpContext $httpContext
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \SafeMage\StoreCode\Model\ValidateStore $storeValidator
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        StoreCookieManagerInterface $storeCookieManager,
        HttpContext $httpContext,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ResponseInterface $response,
        \SafeMage\StoreCode\Model\ValidateStore $storeValidator,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->storeCookieManager = $storeCookieManager;
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->storeValidator = $storeValidator;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Replaces execute method to make store switcher in header work when switching between websites with having store
     * codes setting configured in store view scope.
     *
     * @param \Magento\Store\Controller\Store\SwitchAction $subject
     * @param \Closure $proceed
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(\Magento\Store\Controller\Store\SwitchAction $subject, \Closure $proceed)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.3.1') >= 0) {
            return $proceed();
        }

        $currentActiveStore = $this->storeManager->getStore();
        $storeCode = $this->request->getParam(
            StoreResolver::PARAM_NAME,
            $this->storeCookieManager->getStoreCodeFromCookie()
        );
        if ($currentActiveStore->getCode() == $storeCode) {
            if ($this->storeCookieManager->getStoreCodeFromCookie() !== null) {
                $currentActiveStore = $this->storeManager->getStore($this->storeCookieManager->getStoreCodeFromCookie());
            } else {
                $currentActiveStore = $this->storeManager->getDefaultStoreView();
            }
        }
        $validationResult = $this->storeValidator->validate($storeCode);
        if (isset($validationResult['error'])) {
            $this->messageManager->addErrorMessage($validationResult['error']);
            $subject->getResponse()->setRedirect($this->redirect->getRedirectUrl());
            return;
        }
        $store = $validationResult['store'];
        $defaultStoreView = $this->storeManager->getDefaultStoreView();
        if ($defaultStoreView->getId() == $store->getId()) {
            $this->storeCookieManager->deleteStoreCookie($store);
        } else {
            $this->httpContext->setValue(Store::ENTITY, $store->getCode(), $defaultStoreView->getCode());
            $this->storeCookieManager->setStoreCookie($store);
        }

        //SafeMage fix: we need to define whether we switch to default store as it may not have "default" store code now
        if ($store->isUseStoreInUrl() || !$store->isUseStoreInUrl()
            || ($defaultStoreView->getId() == $store->getId())) {
            // Change store code in redirect url
            if (strpos($this->redirect->getRedirectUrl(), $currentActiveStore->getBaseUrl()) !== false) {
                $subject->getResponse()->setRedirect(
                    str_replace(
                        $currentActiveStore->getBaseUrl(),
                        $store->getBaseUrl(),
                        $this->redirect->getRedirectUrl()
                    )
                );
            } else {
                $subject->getResponse()->setRedirect($store->getBaseUrl());
            }
        } else {
            $subject->getResponse()->setRedirect($this->redirect->getRedirectUrl());
        }
    }
}
