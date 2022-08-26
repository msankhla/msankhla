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
namespace FedEx\CrossBorder\Model\OrderManagement\ReadyForExport;

use FedEx\CrossBorder\Api\Data\OrderManagement\ReadyForExport\InformationInterface;
use FedEx\CrossBorder\Api\Data\OrderManagement\ReadyForExport\OrderInformationInterface;
use Magento\Framework\DataObject;

class OrderInformation extends DataObject implements OrderInformationInterface
{
    /**
     * Returns id
     *
     * @return string
     */
    public function getId()
    {
        return $this->getData(static::ID);
    }

    /**
     * Sets id
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setId($value)
    {
        return $this->setData(static::ID, $value);
    }

    /**
     * Returns date created
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(static::CREATED_AT);
    }

    /**
     * Sets date created
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setCreatedAt($value)
    {
        return $this->setData(static::CREATED_AT, $value);
    }

    /**
     * Returns status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(static::STATUS);
    }

    /**
     * Sets status
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setStatus($value)
    {
        return $this->setData(static::STATUS, $value);
    }

    /**
     * Returns type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getData(static::TYPE);
    }

    /**
     * Sets type
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setType($value)
    {
        return $this->setData(static::TYPE, $value);
    }

    /**
     * Returns information
     *
     * @return InformationInterface
     */
    public function getInformation()
    {
        return $this->getData(static::INFORMATION);
    }

    /**
     * Sets information
     *
     * @param InformationInterface $value
     * @return OrderInformationInterface
     */
    public function setInformation($value)
    {
        return $this->setData(static::INFORMATION, $value);
    }
}
