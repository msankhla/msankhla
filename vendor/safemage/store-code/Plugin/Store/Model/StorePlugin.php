<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/

namespace SafeMage\StoreCode\Plugin\Store\Model;

/**
 * Plugin is needed starting Magento 2.2.x version and up to 2.2.2 where this fix will be implemented in
 * the pluginized method. Is used for correctly parsing store url after switching store views.
 */
class StorePlugin
{
    /**
     * @var \Magento\Framework\Session\SidResolverInterface
     */
    private $sidResolver;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $session;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->sidResolver = $sidResolver;
        $this->url = $url;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->session = $session;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Plugin is needed starting Magento 2.2.x version and up to 2.2.2 where this fix will be implemented in
     * the pluginized method. Is used for correctly parsing store url after switching store views.
     *
     * @param \Magento\Store\Model\Store $subject
     * @param \Closure $proceed
     * @param bool $fromStore
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetCurrentUrl(
        \Magento\Store\Model\Store $subject,
        \Closure $proceed,
        $fromStore = true
    ) {
        if (version_compare($this->productMetadata->getVersion(), '2.1.15') >= 0) {

            /** @var string $requestString Request path without query parameters */
            $requestString = $this->url->escape(
                preg_replace(
                    '/\?.*?$/',
                    '',
                    ltrim($this->request->getRequestString(), '/')
                )
            );

            $storeUrl = $subject->getUrl('', ['_secure' => $this->storeManager->getStore()->isCurrentlySecure()]);

            if (!filter_var($storeUrl, FILTER_VALIDATE_URL)) {
                return $storeUrl;
            }

            $storeParsedUrl = parse_url($storeUrl);

            $storeParsedQuery = [];
            if (isset($storeParsedUrl['query'])) {
                parse_str($storeParsedUrl['query'], $storeParsedQuery);
            }

            $currQuery = $this->request->getQueryValue();

            if (version_compare($this->productMetadata->getVersion(), '2.3.5') < 0) {
                $sidQueryParam = $this->sidResolver->getSessionIdQueryParam($this->_getSession($subject));
                if (isset($currQuery[$sidQueryParam])
                    && !empty($currQuery[$sidQueryParam])
                    && $this->_getSession($subject)->getSessionIdForHost($storeUrl) != $currQuery[$sidQueryParam]
                ) {
                    unset($currQuery[$sidQueryParam]);
                }
            }

            foreach ($currQuery as $key => $value) {
                $storeParsedQuery[$key] = $value;
            }

            if ($fromStore && !$subject->isUseStoreInUrl()) {
                $storeParsedQuery['___store'] = $subject->getCode();
            } elseif (isset($storeParsedQuery['___store'])) {
                unset($storeParsedQuery['___store']);
            }

            if ($fromStore !== false) {
                $storeParsedQuery['___from_store'] = $fromStore ===
                true ? $this->storeManager->getStore()->getCode() : $fromStore;
            }

            $requestStringParts = explode('?', $requestString, 2);
            $requestStringPath = $requestStringParts[0];
            if (isset($requestStringParts[1])) {
                parse_str($requestStringParts[1], $requestString);
            } else {
                $requestString = [];
            }

            $currentUrlQueryParams = array_merge($requestString, $storeParsedQuery);

            $currentUrl = $storeParsedUrl['scheme']
                . '://'
                . $storeParsedUrl['host']
                . (isset($storeParsedUrl['port']) ? ':' . $storeParsedUrl['port'] : '')
                . $storeParsedUrl['path']
                . $requestStringPath
                . ($currentUrlQueryParams ? '?' . http_build_query($currentUrlQueryParams) : '');

            return $currentUrl;
        } else {
            return $proceed($fromStore);
        }
    }

    /**
     * Retrieve store session object.
     *
     * @param \Magento\Store\Model\Store $subject
     * @return \Magento\Framework\Session\SessionManagerInterface
     */
    protected function _getSession(\Magento\Store\Model\Store $subject)
    {
        if (!$this->session->isSessionExists()) {
            $this->session->setName('store_' . $subject->getCode());
            $this->session->start();
        }
        return $this->session;
    }
}
