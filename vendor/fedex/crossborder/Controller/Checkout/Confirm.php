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

use FedEx\Core\Model\Log;
use FedEx\CrossBorder\Helper\Data as Helper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;

class Confirm extends Action
{
    const LOG_FILE                      = 'FedEx/CrossBorder/Confirm.log';

    protected $_helper;

    /**
     * @var CheckoutSession
     */
    protected $_session;

    /**
     * Confirm constructor.
     *
     * @param CheckoutSession $session
     * @param Helper $helper
     * @param Context $context
     */
    public function __construct(
        CheckoutSession $session,
        Helper $helper,
        Context $context
    ) {
        $this->_helper = $helper;
        $this->_session = $session;

        parent::__construct($context);
    }

    /**
     * Adds log
     *
     * @param mixed $message
     * @return $this
     */
    public function addLog($message)
    {
        if ($this->_helper->isLogsEnabled()) {
            Log::Info($message, static::LOG_FILE);
        }

        return $this;
    }

    /**
     * Clear quote
     *
     * @return $this
     */
    public function clearQuote()
    {
        $this->addLog('Clear quote (ID = ' . $this->_session->getQuoteId() . ').');
        $this->_session->clearQuote();

        return $this;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $this->addLog('Order created on FedEx side.');
        $this->clearQuote();
        return $this->resultRedirectFactory->create()->setUrl(
            $this->_url->getUrl('checkout/onepage/success')
        );
    }
}
