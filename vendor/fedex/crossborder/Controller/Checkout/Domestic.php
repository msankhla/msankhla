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
namespace FedEx\CrossBorder\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use FedEx\CrossBorder\Helper\Data as Helper;
use Magento\Framework\App\Action\Context;

class Domestic extends Action
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * Domestic constructor.
     *
     * @param Helper $helper
     * @param Context $context
     */
    public function __construct(
        Helper $helper,
        Context $context
    ) {
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if ($this->_helper->isEnabled()) {
            $this->_helper->saveDefaultCountry();
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout');

            return $resultRedirect;
        }

        $storeManager = $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $url = $storeManager->getStore()->getBaseUrl();
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($url));
    }
}
