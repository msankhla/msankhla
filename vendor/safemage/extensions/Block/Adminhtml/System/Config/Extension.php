<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: https://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (https://www.safemage.com/)
*/

namespace SafeMage\Extensions\Block\Adminhtml\System\Config;

/**
 * Creates custom group with custom template under Store->Configuration->SafeMage->Extensions.
 */
class Extension extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * Return header html for fieldset.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getHeaderHtml($element)
    {
        return parent::_getHeaderHtml($element) . $this->getContentHtml();
    }

    /**
     * Set custom template for the fieldset.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getContentHtml()
    {
        return $this->getLayout()
            ->createBlock(
                \SafeMage\Extensions\Block\Adminhtml\System\Config\Extension\ExtensionsList::class,
                'safemage_extensions_extension_list'
            )
            ->setTemplate('SafeMage_Extensions::extensions/system/list.phtml')
            ->toHtml();
    }
}
