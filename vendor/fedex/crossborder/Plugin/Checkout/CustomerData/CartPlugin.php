<?php
/**
 * FedEx Cross Border component
 *
 * @category    FedEx
 * @package     FedEx_CrossBorder
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\CrossBorder\Plugin\Checkout\CustomerData;

use \Magento\Checkout\CustomerData\Cart;
use FedEx\CrossBorder\Model\Checkout\Widget;

class CartPlugin
{
    /**
     * @var Widget
     */
    protected $_widget;

    /**
     * CartPlugin constructor.
     *
     * @param Widget $widget
     */
    public function __construct(
        Widget $widget
    ) {
        $this->_widget = $widget;
    }

    /**
     * @param Cart $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(Cart $subject, array $result)
    {
        $result['checkout_widget'] = ($this->_widget->getHelper()->isInternational() ? $this->_widget->toHtml() : '');

        return $result;
    }
}