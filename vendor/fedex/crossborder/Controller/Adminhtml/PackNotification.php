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
namespace FedEx\CrossBorder\Controller\Adminhtml;

use FedEx\CrossBorder\Helper\PackNotification as Helper;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification as ResourceModel;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

abstract class PackNotification extends Action
{
    const ADMIN_RESOURCE            = 'FedEx_CrossBorder::pack_notification';

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * View constructor.
     * @param Helper $helper
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        Helper $helper,
        Registry $registry,
        Context $context
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->_helper = $helper;
    }

    /**
     * Validation
     *
     * @return bool
     */
    public function isValid()
    {
        try {
            if (!$this->_helper->isAvailable($this->_helper->getCurrentOrder())) {
                $this->messageManager->addErrorMessage(__(ResourceModel::ERROR_UNAVAILABLE));
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}