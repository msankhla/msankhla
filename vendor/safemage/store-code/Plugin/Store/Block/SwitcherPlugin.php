<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/

namespace SafeMage\StoreCode\Plugin\Store\Block;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\App\ActionInterface;

/**
 * Plugin is needed starting Magento 2.2.x version, need to change redirect url in store switcher,
 * because in case url keys for products/categories differ in different store views you
 * won't be able to switch back to the default view.
 */
class SwitcherPlugin
{
    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    private $postDataHelper;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    private $urlHelper;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->postDataHelper = $postDataHelper;
        $this->urlFinder = $urlFinder;
        $this->request = $request;
        $this->urlHelper = $urlHelper;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Plugin is needed starting Magento 2.2.x version, need to change redirect url in store switcher,
     * because in case url keys for products/categories differ in different store views you
     * won't be able to switch back to the default view.
     *
     * @param \Magento\Store\Block\Switcher $subject
     * @param \Closure $proceed
     * @param \Magento\Store\Model\Store $store
     * @param array $data
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetTargetStorePostData(
        \Magento\Store\Block\Switcher $subject,
        \Closure $proceed,
        \Magento\Store\Model\Store $store,
        $data = []
    ) {
        if (version_compare($this->productMetadata->getVersion(), '2.1.15') >= 0) {
            $urlRewrite = $this->urlFinder->findOneByData([
                UrlRewrite::TARGET_PATH => $this->trimSlashInPath($this->request->getPathInfo()),
                UrlRewrite::STORE_ID => $store->getId(),
            ]);

            if (!$urlRewrite) {
                $urlRewrite = $this->urlFinder->findOneByData([
                    UrlRewrite::REQUEST_PATH => $this->trimSlashInPath($this->request->getOriginalPathInfo()),
                    UrlRewrite::STORE_ID => $store->getId(),
                ]);
            }

            $data[\Magento\Store\Api\StoreResolverInterface::PARAM_NAME] = $store->getCode();
            if ($urlRewrite) {
                $url = $store->getUrl($urlRewrite->getRequestPath());
                $data[ActionInterface::PARAM_NAME_URL_ENCODED] = $this->urlHelper->getEncodedUrl(
                    $this->trimSlashInPath($url)
                );
            }

            return $this->postDataHelper->getPostData(
                $store->getCurrentUrl(false),
                $data
            );
        } else {
            return $proceed($store, $data);
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function trimSlashInPath($path)
    {
        return trim($path, '/');
    }
}
