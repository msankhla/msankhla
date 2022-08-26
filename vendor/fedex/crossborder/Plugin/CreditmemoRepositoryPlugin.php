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
namespace FedEx\CrossBorder\Plugin;

use FedEx\CrossBorder\Model\RefundManagement;
use Magento\Framework\Registry;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;

class CreditmemoRepositoryPlugin
{
    /**
     * @var RefundManagement
     */
    protected $_refundManagement;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * CreditmemoRepositoryPlugin constructor.
     *
     * @param RefundManagement $refundManagement
     * @param Registry $registry
     */
    public function __construct(
        RefundManagement $refundManagement,
        Registry $registry
    ) {
        $this->_refundManagement = $refundManagement;
        $this->_registry = $registry;
    }

    /**
     * @param CreditmemoRepositoryInterface $subject
     * @param CreditmemoInterface $result
     * @return CreditmemoInterface
     * @throws \Exception
     */
    public function afterSave(
        CreditmemoRepositoryInterface $subject,
        CreditmemoInterface $result
    ) {
        if (!$this->_registry->registry('skipRefundRequest')) {
            $this->_refundManagement->send($result);
        }

        return $result;
    }
}
