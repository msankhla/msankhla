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
namespace FedEx\CrossBorder\Model\OrderManagement\OrderInformation;

use FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformation\ProductInformationInterface;
use Magento\Framework\DataObject;

class ProductInformation extends DataObject implements ProductInformationInterface
{
    /**
     * Returns product id
     *
     * @return mixed
     */
    public function getProductId()
    {
        return $this->getData(static::PRODUCT_ID);
    }

    /**
     * Sets product id
     *
     * @param mixed $value
     * @return ProductInformationInterface
     */
    public function setProductId($value)
    {
        return $this->setData(static::PRODUCT_ID, $value);
    }

    /**
     * Returns qty
     *
     * @return float
     */
    public function getQty()
    {
        return $this->getData(static::QTY);
    }

    /**
     * Sets qty
     *
     * @param float $value
     * @return ProductInformationInterface
     */
    public function setQty($value)
    {
        return $this->setData(static::QTY, (float) $value);
    }
}
