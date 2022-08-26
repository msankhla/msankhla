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
namespace FedEx\CrossBorder\Block\Adminhtml\Order\View\Tab;

use FedEx\CrossBorder\Helper\PackNotification as Helper;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Text\ListText;
use Magento\Sales\Model\Order;

class PackNotification extends ListText implements
    TabInterface
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * PackNotification constructor.
     *
     * @param Context $context
     * @param Helper $helper
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Helper $helper,
        Registry $registry,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * Checks if tab can be shown
     *
     * @return bool
     */
    public function canShowTab()
    {
        return $this->_helper->isAvailable(
            $this->getOrder()
        );
    }

    /**
     * Checks if tab hidden
     *
     * @return false
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Returns current order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_registry->registry('current_order');
    }

    /**
     * Returns tab label
     *
     * @return Phrase|string
     */
    public function getTabLabel()
    {
        return __('Pack Notifications');
    }

    /**
     * Returns tab title
     *
     * @return Phrase|string
     */
    public function getTabTitle()
    {
        return __('Order Pack Notifications');
    }
}
