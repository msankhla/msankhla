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
namespace FedEx\CrossBorder\Model\OrderLink;

use FedEx\CrossBorder\Api\Data\OrderLink\AddressInterface;
use FedEx\CrossBorder\Model\ResourceModel\OrderLink\Address as ResourceModel;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

class Address extends AbstractModel implements AddressInterface
{
    /**
     * @var CountryFactory
     */
    protected $_countryFactory;

    /**
     * Address constructor.
     *
     * @param CountryFactory $countryFactory
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        CountryFactory $countryFactory,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_countryFactory = $countryFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
    
    /**
     * Returns order link id
     *
     * @return int
     */
    public function getOrderLinkId()
    {
        return (int) $this->getData(static::ORDER_LINK_ID);
    }

    /**
     * Sets order link id
     *
     * @param int $value
     * @return AddressInterface
     */
    public function setOrderLinkId($value)
    {
        return $this->setData(static::ORDER_LINK_ID, (int) $value);
    }

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
     * @return AddressInterface
     */
    public function setFirstname($value)
    {
        return $this->setData(static::FIRSTNAME, $value);
    }

    /**
     * Returns full name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstname() .
            ($this->getFirstname() && $this->getLastname() ? ' ' : '') .
            $this->getLastname();
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
     * @return AddressInterface
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
     * @return AddressInterface
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
     * @return AddressInterface
     */
    public function setCity($value)
    {
        return $this->setData(static::CITY, $value);
    }

    /**
     * Returns country name
     *
     * @param string|null $code
     * @return string
     */
    public function getCountry($code = null)
    {
        if ($this->getCountryId()) {
            $country = $this->_countryFactory->create(
            )->loadByCode(
                isset($code) ? $code : $this->getCountryId()
            );

            return $country->getName();
        }

        return '';
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
     * @return AddressInterface
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
     * @return AddressInterface
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
     * @return AddressInterface
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
     * @return AddressInterface
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
     * @return AddressInterface
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
     * @return AddressInterface
     */
    public function setCompany($value)
    {
        return $this->setData(static::COMPANY, $value);
    }
}