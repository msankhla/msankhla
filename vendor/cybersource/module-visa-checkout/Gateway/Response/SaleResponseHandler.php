<?php
/**
 * Copyright © 2018 CollinsHarper. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\VisaCheckout\Gateway\Response;

use CyberSource\VisaCheckout\Gateway\Helper\SubjectReader;
use CyberSource\VisaCheckout\Gateway\Http\Client\SOAPClient;
use CyberSource\VisaCheckout\Gateway\Http\TransferFactory;
use CyberSource\VisaCheckout\Helper\RequestDataBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Response\HandlerInterface;

class SaleResponseHandler extends AbstractResponseHandler implements HandlerInterface
{
    /**
     * @var RequestDataBuilder
     */
    private $requestDataBuilder;

    /**
     * @var SOAPClient
     */
    private $soapClient;

    /**
     * @var TransferFactory
     */
    private $transferFactory;

    public function __construct(
        RequestDataBuilder $requestDataBuilder,
        SOAPClient $SOAPClient,
        TransferFactory $transferFactory,
        SubjectReader $subjectReader
    ) {
        $this->requestDataBuilder = $requestDataBuilder;
        $this->soapClient = $SOAPClient;
        $this->transferFactory = $transferFactory;

        parent::__construct($subjectReader);
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var $payment \Magento\Sales\Model\Order\Payment */
        $payment = $this->getValidPaymentInstance($handlingSubject);
        $payment = $this->handleAuthorizeResponse($payment, $response);

        if ($response[self::REASON_CODE] === 480) {
            $payment->setIsTransactionClosed(0);
            $payment->setIsTransactionPending(true);
        } else {
            $payment->setIsTransactionClosed(1);
            $payment->setIsTransactionPending(false);
            $payment->setShouldCloseParentTransaction(false);
        }
    }
}
