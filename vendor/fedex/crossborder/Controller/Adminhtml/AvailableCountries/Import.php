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
namespace FedEx\CrossBorder\Controller\Adminhtml\AvailableCountries;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use FedEx\CrossBorder\Model\AvailableCountries\Import as ImportModel;

class Import extends Action
{
    const ADMIN_RESOURCE    = 'FedEx_CrossBorder::available_countries';
    const MSG_SUCCESS       = 'Import successfully completed.';

    protected $_import;

    /**
     * Import constructor.
     * @param Context $context
     * @param ImportModel $import
     */
    public function __construct(
        Context $context,
        ImportModel $import
    ) {
        $this->_import = $import;

        parent::__construct($context);
    }

    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->_import->execute()->hasError()) {
            $this->messageManager->addErrorMessage(__($this->_import->getErrorMessage()));
        } else {
            $this->messageManager->addSuccessMessage(__(static::MSG_SUCCESS));
        }

        return $resultRedirect->setPath('*/*/');
    }
}