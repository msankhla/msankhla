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
namespace FedEx\CrossBorder\Model;

use FedEx\CrossBorder\Api\OrderLinkManagementInterface;
use FedEx\CrossBorder\Api\Data\OrderLinkInterfaceFactory;
use FedEx\CrossBorder\Api\Data\QuoteLinkInterfaceFactory;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;

class OrderLinkManagement implements OrderLinkManagementInterface
{
    /**
     * @var MerchantControl
     */
    protected $_merchantControl;

    /**
     * @var OrderExtensionFactory
     */
    protected $_orderExtensionFactory;

    /**
     * @var OrderLinkInterfaceFactory
     */
    protected $_orderLinkFactory;

    /**
     * @var QuoteLinkInterfaceFactory
     */
    protected $_quoteLinkFactory;

    /**
     * OrderLinkManagement constructor.
     *
     * @param MerchantControl $merchantControl
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param OrderLinkInterfaceFactory $orderLinkFactory
     * @param QuoteLinkInterfaceFactory $quoteLinkFactory
     */
    public function __construct(
        MerchantControl $merchantControl,
        OrderExtensionFactory $orderExtensionFactory,
        OrderLinkInterfaceFactory $orderLinkFactory,
        QuoteLinkInterfaceFactory $quoteLinkFactory
    ) {
        $this->_merchantControl = $merchantControl;
        $this->_orderExtensionFactory = $orderExtensionFactory;
        $this->_orderLinkFactory = $orderLinkFactory;
        $this->_quoteLinkFactory = $quoteLinkFactory;
    }

    /**
     * Returns merchant control
     *
     * @return MerchantControl
     */
    public function getMerchantControl()
    {
        return $this->_merchantControl;
    }

    /**
     * Link FedEx data to cart quote
     *
     * @param OrderInterface $order
     * @return $this
     */
    public function setFdxcbData(OrderInterface $order)
    {
        $orderExtension = ($order->getExtensionAttributes()) ?: $this->_orderExtensionFactory->create();
        if (!$orderExtension->getFdxcbData()) {
            /** @var OrderLink $orderLink */
            $orderLink = $this->_orderLinkFactory->create();
            $orderLink->load($order->getId(), 'order_id');

            if (!$orderLink->getId()) {
                if ($order->getQuoteId()) {
                    $quoteLink = $this->_quoteLinkFactory->create();
                    $quoteLink->load(
                        $order->getQuoteId(),
                        'quote_id'
                    );

                    if ($quoteLink->getId()) {
                        $orderLink->setData(
                            $quoteLink->getData()
                        )->setOrderId(
                            $order->getId()
                        )->unsetData(
                            'entity_id'
                        );

                        if (!$this->getMerchantControl()->isEnabled()) {
                            $address = $orderLink->getOriginalShippingAddress(true);
                            $fields = $address->getResource()->getConnection()->describeTable(
                                $address->getResource()->getMainTable()
                            );

                            foreach (array_keys($fields) as $field) {
                                if ($field !== $address->getResource()->getIdFieldName()) {
                                    $address->setData(
                                        $field,
                                        $order->getShippingAddress()->getData($field)
                                    );
                                }
                            }
                        }

                        $orderLink->save();
                    }
                }
            }

            if ($orderLink->getId()) {
                $orderExtension->setFdxcbData($orderLink);
                $order->setExtensionAttributes($orderExtension);
            }
        }

        return $this;
    }
}
