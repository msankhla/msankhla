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
namespace FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformation;

interface ProductInformationInterface
{
    const PRODUCT_ID            = 'product_id';
    const QTY                   = 'qty';

    /**
     * Returns product id
     *
     * @return mixed
     */
    public function getProductId();

    /**
     * Sets product id
     *
     * @param mixed $value
     * @return ProductInformationInterface
     */
    public function setProductId($value);

    /**
     * Returns qty
     *
     * @return float
     */
    public function getQty();

    /**
     * Sets qty
     *
     * @param float $value
     * @return ProductInformationInterface
     */
    public function setQty($value);

}
