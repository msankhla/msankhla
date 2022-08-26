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
namespace FedEx\CrossBorder\Model\Payment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order\Payment;
use FedEx\CrossBorder\Model\Carrier\Shipping;

class General extends AbstractMethod
{
    const CODE                      = 'fdxcb';
    const ERROR_SHIPPING_METHOD     = 'This payment type availale only with shipping method "FedEx Cross Border".';
    /**
     * @var string
     */
    protected $_code                = self::CODE;

    /**
     * Check whether payment method can be used
     *
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        $result = parent::isAvailable($quote);
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();

        return ($result && strpos($shippingMethod, Shipping::CODE . '_') === 0);
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @deprecated 100.2.0
     */
    public function validate()
    {
        parent::validate();

        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Payment) {
            $shippingMethod = $paymentInfo->getOrder()->getShippingMethod();
        } else {
            $shippingMethod = $paymentInfo->getQuote()->getShippingAddress()->getShippingMethod();
        }

        if (strpos($shippingMethod, Shipping::CODE . '_') !== 0) {
            throw new LocalizedException(
                __(static::ERROR_SHIPPING_METHOD)
            );
        }

        return $this;
    }
}