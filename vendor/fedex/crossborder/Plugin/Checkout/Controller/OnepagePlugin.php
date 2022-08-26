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
namespace FedEx\CrossBorder\Plugin\Checkout\Controller;

use Magento\Checkout\Controller\Onepage;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface;
use FedEx\CrossBorder\Helper\Data as Helper;

class OnepagePlugin
{
    /**
     * @var array
     */
    protected $_patternsList = [
        '/checkout_index_index/',
        '/checkout_onepage_saveOrder/'
    ];

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var RedirectFactory
     */
    protected $_resultRedirectFactory;

    /**
     * OnepagePlugin constructor.
     *
     * @param Helper $helper
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        Helper $helper,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->_helper = $helper;
        $this->_messageManager = $messageManager;
        $this->_resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Checks if action valid
     *
     * @param string $action
     * @return bool
     */
    public function valid($action)
    {
        foreach ($this->_patternsList as $pattern) {
            if (preg_match($pattern, $action)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Around dispatch plugin
     *
     * @param Onepage $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return ResponseInterface|Redirect
     * @throws NotFoundException
     */
    public function aroundDispatch(
        Onepage $subject,
        callable $proceed,
        RequestInterface $request
    ) {
        if ($this->_helper->isInternational() && !$this->valid($request->getFullActionName())) {
            $this->_messageManager->addErrorMessage(__('One-page checkout is not available for international shipping.'));
            return $this->_resultRedirectFactory->create()->setPath('checkout/cart');
        }

        return $proceed($request);
    }
}