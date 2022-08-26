<?php

namespace Corra\Log\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class EnableGraylogButton
 *
 * Corra\Log\Block\Adminhtml\System\Config\Form\Field
 */
class EnableGraylogButton extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setData('value', __("Enable"));
        $element->setData('class', "action-default");
        return parent::_getElementHtml($element);
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->_urlBuilder->getUrl('corra_log/system_config/enable_graylog');
    }
}
