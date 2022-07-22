<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/

namespace SafeMage\Extensions\Plugin\Config\Model\Config\Structure\Element;

class Tab
{
    /**
     * @var \SafeMage\Extensions\Model\ModuleList\Module\ModuleInfoProvider
     */
    private $moduleInfoProvider;

    /**
     * @param \SafeMage\Extensions\Model\ModuleList\Module\ModuleInfoProvider $moduleInfoProvider
     */
    public function __construct(\SafeMage\Extensions\Model\ModuleList\Module\ModuleInfoProvider $moduleInfoProvider)
    {
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    /**
     * @param \Magento\Config\Model\Config\Structure\Element\Tab $subject
     * @param \Magento\Framework\Phrase $result
     * @return string
     */
    public function afterGetLabel($subject, $result)
    {
        if (is_object($result) && $result->getText() == 'SafeMage') {
            return $this->getLogoImg();
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getLogoImg()
    {
        return '<img src="' . $this->getLogoUrl() . '" alt="SafeMage" height="19" />';
    }

    /**
     * Get logo URL.
     *
     * @return string
     */
    private function getLogoUrl()
    {
        return 'https://www.safemage.com/cache/' . $this->moduleInfoProvider->getCacheKey() . '/logo2.png';
    }
}
