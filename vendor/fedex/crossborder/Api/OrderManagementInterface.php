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
namespace FedEx\CrossBorder\Api;

/**
 * Interface for managing order information
 * @api
 */
interface OrderManagementInterface
{
    /**
     * Create order
     *
     * @param \FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformationInterface $orderInformation
     * @return \FedEx\CrossBorder\Api\Data\ResultInterface
     */
    public function create(
        \FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformationInterface $orderInformation
    );
}
