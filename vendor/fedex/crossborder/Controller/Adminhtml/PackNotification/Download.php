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
namespace FedEx\CrossBorder\Controller\Adminhtml\PackNotification;

use FedEx\CrossBorder\Controller\Adminhtml\PackNotification as PackNotificationAction;
use FedEx\CrossBorder\Helper\PackNotification as Helper;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Registry;

class Download extends PackNotificationAction
{
    /**
     * @var RedirectFactory
     * @var RedirectFactory
     */
    protected $_redirectFactory;

    /**
     * Download constructor.
     *
     * @param RedirectFactory $redirectFactory
     * @param Helper $helper
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        Helper $helper,
        Registry $registry,
        Context $context
    ) {
        $this->_redirectFactory = $redirectFactory;
        parent::__construct($helper, $registry, $context);
    }

    public function execute()
    {
        if ($this->isValid()) {
            try {
                $downloadResult = $this->_helper->getCurrentPackNotification()->download();
                return $downloadResult ?
                    $downloadResult :
                    $this->_redirect('*/*/view', ['pack_id' => $this->_helper->getCurrentPackNotificationId()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(
                    __($e->getMessage())
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __($e->getMessage())
                );
            }
        }

        $this->_redirect('*/*/view', ['pack_id' => $this->_helper->getCurrentPackNotificationId()]);
    }
}
