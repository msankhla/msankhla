<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/

namespace SafeMage\Extensions\Plugin\Config\Block\System\Config;

class Tabs
{
    /**
     * @var \SafeMage\Extensions\Plugin\Config\Model\Config\Structure\Element\Tab
     */
    private $tab;

    /**
     * @param \SafeMage\Extensions\Plugin\Config\Model\Config\Structure\Element\Tab $tab
     */
    public function __construct(\SafeMage\Extensions\Plugin\Config\Model\Config\Structure\Element\Tab $tab)
    {
        $this->tab = $tab;
    }

    /**
     * @param \Magento\Config\Block\System\Config\Tabs $subject
     * @param string $result
     * @param string|array $data
     * @param array|null $allowedTags
     * @return string
     */
    public function afterEscapeHtml($subject, $result, $data, $allowedTags = null)
    {
        if (strpos($result, 'SafeMage') !== false) {
            $result = $this->tab->getLogoImg();
        }
        return $result;
    }
}
