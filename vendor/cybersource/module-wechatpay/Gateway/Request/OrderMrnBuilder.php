<?php
/**
 * Copyright © 2021 CyberSource. All rights reserved.
 * See accompanying LICENSE.txt for applicable terms of use and license.
 */

namespace CyberSource\WeChatPay\Gateway\Request;

class OrderMrnBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \CyberSource\WeChatPay\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @param \CyberSource\WeChatPay\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \CyberSource\WeChatPay\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        return ['merchantReferenceCode' => $paymentDO->getOrder()->getOrderIncrementId()];
    }
}
