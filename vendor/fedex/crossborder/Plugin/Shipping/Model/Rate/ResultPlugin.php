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
namespace FedEx\CrossBorder\Plugin\Shipping\Model\Rate;

use FedEx\Core\Model\Log;
use FedEx\CrossBorder\Helper\Data as Helper;
use FedEx\CrossBorder\Model\Carrier\Shipping;
use Magento\Shipping\Model\Rate\Result;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\AbstractResult;

class ResultPlugin
{
    const MSG_METHOD_ADDED      = 'The method "%s" was added';
    const MSG_METHOD_REMOVED    = 'The method "%s" was removed';

    protected $_helper;

    /**
     * ResultPlugin constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Adds log
     *
     * @param string $message
     * @return $this
     */
    public function addLog($message)
    {
        if ($this->_helper->isLogsEnabled()) {
            Log::Info($message, Shipping::LOG_FILE);
        }

        return $this;
    }

    /**
     * Plugin for append method
     *
     * @param Result $subject
     * @param callable $proceed
     * @param $result
     * @return Result
     */
    public function aroundAppend(
        Result $subject,
        callable $proceed,
        $result
    ) {
        if (!$this->_helper->isDomesticShipping() &&
            ($result instanceof Error || $result instanceof AbstractResult)
        ) {
            if (($this->_helper->isInternational() && $result->getCarrier() != Shipping::CODE) ||
                (!$this->_helper->isInternational() && $result->getCarrier() == Shipping::CODE)
            ) {
                $this->addLog(sprintf(
                    static::MSG_METHOD_REMOVED,
                    $result->getCarrier() . '_' . $result->getMethod()
                ));
                return $subject;
            }
        }

        if ($result instanceof AbstractResult) {
            $this->addLog(sprintf(
                static::MSG_METHOD_ADDED,
                $result->getCarrier() . '_' . $result->getMethod()
            ));
        }

        return $proceed($result);
    }
}
