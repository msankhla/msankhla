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

use FedEx\CrossBorder\Api\Data\OrderManagement\OrderInformation\AddressInformationInterface;
use Magento\Framework\DataObject;

class AddressInformation extends DataObject implements AddressInformationInterface
{
    /**
     * Returns first name
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->getData(static::FIRSTNAME);
    }

    /**
     * Sets first name
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setFirstname($value)
    {
        return $this->setData(static::FIRSTNAME, $value);
    }

    /**
     * Returns last name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->getData(static::LASTNAME);
    }

    /**
     * Sets last name
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setLastname($value)
    {
        return $this->setData(static::LASTNAME, $value);
    }

    /**
     * Returns street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->getData(static::STREET);
    }

    /**
     * Sets street
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setStreet($value)
    {
        return $this->setData(static::STREET, $value);
    }

    /**
     * Returns city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->getData(static::CITY);
    }

    /**
     * Sets city
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setCity($value)
    {
        return $this->setData(static::CITY, $value);
    }

    /**
     * Returns country code
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->getData(static::COUNTRY_ID);
    }

    /**
     * Sets country code
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setCountryId($value)
    {
        return $this->setData(static::COUNTRY_ID, $value);
    }

    /**
     * Returns region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->getData(static::REGION);
    }

    /**
     * Sets region
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setRegion($value)
    {
        return $this->setData(static::REGION, $value);
    }

    /**
     * Returns postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->getData(static::POSTCODE);
    }

    /**
     * Sets postcode
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setPostcode($value)
    {
        return $this->setData(static::POSTCODE, $value);
    }

    /**
     * Returns telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return $this->getData(static::TELEPHONE);
    }

    /**
     * Sets telephone
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setTelephone($value)
    {
        return $this->setData(static::TELEPHONE, $value);
    }

    /**
     * Returns fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->getData(static::FAX);
    }

    /**
     * Sets fax
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setFax($value)
    {
        return $this->setData(static::FAX, $value);
    }

    /**
     * Returns company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->getData(static::COMPANY);
    }

    /**
     * Sets company
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setCompany($value)
    {
        return $this->setData(static::COMPANY, $value);
    }
}
