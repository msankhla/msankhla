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
namespace FedEx\CrossBorder\Plugin\Catalog\Block;

use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\View\Element\Template;
use FedEx\CrossBorder\Helper\Data as Helper;

class ShortcutButtonsPlugin
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * ShortcutButtonsPlugin constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * @param ShortcutButtons $subject
     * @param $proceed
     * @param Template $block
     */
    public function aroundAddShortcut(
        ShortcutButtons $subject,
        $proceed,
        Template $block
    ) {
        if (!$this->_helper->isInternational()) {
            $proceed($block);
        }
    }
}