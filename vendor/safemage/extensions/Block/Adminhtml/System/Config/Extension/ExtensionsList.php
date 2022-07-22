<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/

namespace SafeMage\Extensions\Block\Adminhtml\System\Config\Extension;

/**
 * Outputs SafeMage modules block.
 */
class ExtensionsList extends \Magento\Backend\Block\Template
{
    /**
     * @var \SafeMage\Extensions\Model\ModuleList\Module\ModuleInfoProvider
     */
    private $moduleInfoProvider;

    /**
     * @var \SafeMage\Extensions\Model\System\Config\Source\Enabled
     */
    private $optionsProvider;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \SafeMage\Extensions\Model\ModuleList\Module\ModuleInfoProvider $moduleInfoProvider
     * @param \SafeMage\Extensions\Model\System\Config\Source\Enabled $optionsProvider
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \SafeMage\Extensions\Model\ModuleList\Module\ModuleInfoProvider $moduleInfoProvider,
        \SafeMage\Extensions\Model\System\Config\Source\Enabled $optionsProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleInfoProvider = $moduleInfoProvider;
        $this->optionsProvider = $optionsProvider;
        $this->moduleManager = $moduleManager;

    }

    /**
     * Retrieve SafeMage extensions list.
     *
     * @return array
     */
    public function getExtensionsList()
    {
        return $this->moduleInfoProvider->getModulesList();
    }

    /**
     * Retrieve extension URL.
     *
     * @param array $config
     * @return string
     */
    public function getExtensionUrl(array $config)
    {
        if (empty($config['url'])) {
            return '';
        }
        return 'https://www.safemage.com/' . $config['url'] . '.html';
    }

    /**
     * Get extension image URL.
     *
     * @param string $code
     * @return string
     */
    public function getImageUrl($code)
    {
        return 'https://www.safemage.com/cache/'
        . $this->moduleInfoProvider->getCacheKey() . '/' . strtolower($code) . '_m2.jpg';
    }

    /**
     * Get current magento mode.
     * @return string
     */
    public function getMagentoMode()
    {
        return (string)$this->_appState->getMode();
    }

    /**
     * Get module enabled/disabled select.
     *
     * @param string $code
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEnableSelectHtml($code = '')
    {
        $select = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Html\Select::class)
            ->setData([
                    'id' => 'safemage_extensions_' . $code . '_enabled',
                    'class' => 'select safemage-extension-enabled-select'
                ])
            ->setName('groups[extension][fields][enabled][' . $code . ']')
            ->setOptions($this->optionsProvider->toOptionArray())

            ->setValue($this->moduleManager->isEnabled($code) ? 1 : 0);

        if ($this->_appState->getMode() == \Magento\Framework\App\State::MODE_PRODUCTION) {
            $select->setExtraParams('disabled="disabled"');
        }

        return $select->getHtml();
    }
}
