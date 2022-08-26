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
namespace FedEx\CrossBorder\Api\Data\OrderLink;

interface AddressInterface extends \FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformation\AddressInformationInterface
{
    const ORDER_LINK_ID         = 'order_link_id';

    /**
     * Returns order link id
     *
     * @return int
     */
    public function getOrderLinkId();

    /**
     * Sets order link id
     *
     * @param int $value
     * @return AddressInterface
     */
    public function setOrderLinkId($value);
}
