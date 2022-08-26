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

interface AddressInformationInterface
{
    const FIRSTNAME             = 'firstname';
    const LASTNAME              = 'lastname';
    const STREET                = 'street';
    const CITY                  = 'city';
    const COUNTRY_ID            = 'country_id';
    const REGION                = 'region';
    const POSTCODE              = 'postcode';
    const TELEPHONE             = 'telephone';
    const FAX                   = 'fax';
    const COMPANY               = 'company';

    /**
     * Returns first name
     *
     * @return string
     */
    public function getFirstname();

    /**
     * Sets first name
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setFirstname($value);

    /**
     * Returns last name
     *
     * @return string
     */
    public function getLastname();

    /**
     * Sets last name
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setLastname($value);

    /**
     * Returns street
     *
     * @return string
     */
    public function getStreet();

    /**
     * Sets street
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setStreet($value);

    /**
     * Returns city
     *
     * @return string
     */
    public function getCity();

    /**
     * Sets city
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setCity($value);

    /**
     * Returns country code
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Sets country code
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setCountryId($value);

    /**
     * Returns region
     *
     * @return string
     */
    public function getRegion();

    /**
     * Sets region
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setRegion($value);

    /**
     * Returns postcode
     *
     * @return string
     */
    public function getPostcode();

    /**
     * Sets postcode
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setPostcode($value);

    /**
     * Returns telephone
     *
     * @return string
     */
    public function getTelephone();

    /**
     * Sets telephone
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setTelephone($value);

    /**
     * Returns fax
     *
     * @return string
     */
    public function getFax();

    /**
     * Sets fax
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setFax($value);

    /**
     * Returns company
     *
     * @return string
     */
    public function getCompany();

    /**
     * Sets company
     *
     * @param string $value
     * @return AddressInformationInterface
     */
    public function setCompany($value);

}
