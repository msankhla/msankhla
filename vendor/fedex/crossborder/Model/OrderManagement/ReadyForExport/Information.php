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

use FedEx\CrossBorder\Api\Data\OrderManagement\ReadyForExport\DocumentInterface;
use FedEx\CrossBorder\Api\Data\OrderManagement\ReadyForExport\InformationInterface;
use Magento\Framework\DataObject;

class Information extends DataObject implements InformationInterface
{
    /**
     * Returns data
     *
     * @return DocumentInterface[]
     */
    public function getDocuments()
    {
        return $this->getData(static::DOCUMENTS);
    }

    /**
     * Sets data
     *
     * @param DocumentInterface[] $value
     * @return InformationInterface
     */
    public function setDocuments($value)
    {
        return $this->setData(static::DOCUMENTS, $value);
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
     * @return InformationInterface
     */
    public function setStatus($value)
    {
        return $this->setData(static::STATUS, $value);
    }

    /**
     * Returns pack notification id
     *
     * @return string
     */
    public function getIdPackNotification()
    {
        return $this->getData(static::ID_PACK_NOTIFICATION);
    }

    /**
     * Sets pack notification id
     *
     * @param string $value
     * @return InformationInterface
     */
    public function setIdPackNotification($value)
    {
        return $this->setData(static::ID_PACK_NOTIFICATION, $value);
    }

    /**
     * Returns retailer order number
     *
     * @return string
     */
    public function getRetailerOrderNumber()
    {
        return $this->getData(static::RETAILER_ORDER_NUMBER);
    }

    /**
     * Sets retailer order number
     *
     * @param string $value
     * @return InformationInterface
     */
    public function setRetailerOrderNumber($value)
    {
        return $this->setData(static::RETAILER_ORDER_NUMBER, $value);
    }

    /**
     * Returns documents url
     *
     * @return string
     */
    public function getDocumentsUrl()
    {
        return $this->getData(static::DOCUMENTS_URL);
    }

    /**
     * Sets documents url
     *
     * @param string $value
     * @return InformationInterface
     */
    public function setDocumentsUrl($value)
    {
        return $this->setData(static::DOCUMENTS_URL, $value);
    }

    /**
     * Returns cancel url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getData(static::CANCEL_URL);
    }

    /**
     * Sets cancel url
     *
     * @param string $value
     * @return InformationInterface
     */
    public function setCancelUrl($value)
    {
        return $this->setData(static::CANCEL_URL, $value);
    }

    /**
     * Returns tracking number
     *
     * @return string
     */
    public function getTrackingNumber()
    {
        return $this->getData(static::TRACKING_NUMBER);
    }

    /**
     * Sets tracking number
     *
     * @param string $value
     * @return InformationInterface
     */
    public function setTrackingNumber($value)
    {
        return $this->setData(static::TRACKING_NUMBER, $value);
    }

    /**
     * Returns order id
     *
     * @return string
     */
    public function getIdorder()
    {
        return $this->getData(static::IDORDER);
    }

    /**
     * Sets order_id
     *
     * @param string $value
     * @return InformationInterface
     */
    public function setIdorder($value)
    {
        return $this->setData(static::IDORDER, $value);
    }
}
