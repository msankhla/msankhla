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
namespace FedEx\CrossBorder\Controller\WelcomeMat;

use FedEx\CrossBorder\Helper\Data as Helper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Save extends Action
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * Save constructor.
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
     * @return void
     */
    public function execute()
    {
        if ($this->_helper->isEnabled() && $data = $this->getRequest()->getPostValue()) {
            if (isset($data['wm_default']) && $data['wm_default']) {
                $this->_helper->saveDefaultCountry();
            } else {
                if (isset($data['country_selector'])) {
                    $this->_helper->saveSelectedCountry(
                        $data['country_selector'],
                        empty($data['currency_selector'])
                    );
                }

                if (!empty($data['currency_selector'])) {
                    $this->_helper->saveSelectedCurrency($data['currency_selector']);
                }
            }
        }

        $storeManager = $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $storeUrl = $storeManager->getStore()->getBaseUrl();
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($storeUrl));
    }
}
