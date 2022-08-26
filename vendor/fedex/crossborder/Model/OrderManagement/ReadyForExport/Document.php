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
use Magento\Framework\DataObject;

class Document extends DataObject implements DocumentInterface
{
    /**
     * Returns id
     *
     * @return string
     */
    public function getIdDoc()
    {
        return $this->getData(static::ID_DOC);
    }

    /**
     * Sets id
     *
     * @param string $value
     * @return DocumentInterface
     */
    public function setIdDoc($value)
    {
        return $this->setData(static::ID_DOC, $value);
    }

    /**
     * Returns name
     *
     * @return string
     */
    public function getDocName()
    {
        return $this->getData(static::DOC_NAME);
    }

    /**
     * Sets name
     *
     * @param string $value
     * @return DocumentInterface
     */
    public function setDocName($value)
    {
        return $this->setData(static::DOC_NAME, $value);
    }

    /**
     * Returns url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getData(static::URL);
    }

    /**
     * Sets url
     *
     * @param string $value
     * @return DocumentInterface
     */
    public function setUrl($value)
    {
        return $this->setData(static::URL, $value);
    }

    /**
     * Returns format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->getData(static::FORMAT);
    }

    /**
     * Sets format
     *
     * @param string $value
     * @return DocumentInterface
     */
    public function setFormat($value)
    {
        return $this->setData(static::FORMAT, $value);
    }

    /**
     * Returns requires_physical_copy flag value
     *
     * @return bool
     */
    public function getRequiresPhysicalCopy()
    {
        return (bool) $this->getData(static::REQUIRES_PHYSICAL_COPY);
    }

    /**
     * Sets requires_physical_copy flag value
     *
     * @param bool $value
     * @return DocumentInterface
     */
    public function setRequiresPhysicalCopy($value)
    {
        return $this->setData(static::REQUIRES_PHYSICAL_COPY, (bool) $value);
    }
}
