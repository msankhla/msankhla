<?php
/**
 * @package     BlueAcorn/Core
 * @version     1.0.3
 * @author      Blue Acorn iCi <code@blueacorn.com>
 * @author      Greg Harvell <greg@blueacorn.com>
 * @copyright   Copyright © 2019. All Rights Reserved.
 */

namespace BlueAcorn\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\LayoutInterface;

class Data extends AbstractHelper
{

    /**
     * @var BlockFactory \Magento\Framework\View\Element\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * Data constructor.
     * @param Context $context
     * @param BlockFactory $blockFactory
     * @param LayoutInterface $layout
     */
    public function __construct(
        Context $context,
        BlockFactory $blockFactory,
        LayoutInterface $layout
    ) {

        $this->_layout = $layout;
        $this->_blockFactory = $blockFactory;
        parent::__construct($context);
    }

    /**
     * @param $config
     * @param string $scope
     * @return mixed
     */
    public function getConfig($config, $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($config, $scope);
    }

    /**
     * Get CMS Block Content from the Database
     * @param $config
     * @return mixed
     */
    public function getCmsBlock($config)
    {
        return $this->_layout->createBlock('Magento\Cms\Block\Block')
            ->setBlockId($config)
            ->toHtml();
    }

    /**
     * @param $config
     * @return mixed
     */
    public function getCmsBlockFromConfig($config)
    {
        return $this->getCmsBlock($this->getConfig($config));
    }

    /**
     * @param $config
     * @return string
     */
    public function getImageFromConfig($config)
    {
        $folderName = \BlueAcorn\Core\Model\Config\Backend\Image::UPLOAD_DIR;
        $storeLogoPath = $this->getConfig(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $folderName . '/' . $storeLogoPath;
        $imageUrl = $this->_urlBuilder
                ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;

        return $imageUrl;
    }
}
