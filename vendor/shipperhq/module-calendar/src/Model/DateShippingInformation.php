<?php
/**
 *
 * ShipperHQ
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Shipper HQ Shipping
 *
 * @category ShipperHQ
 * @package ShipperHQ_Common
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\Calendar\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use ShipperHQ\Calendar\Api\Data\DateShippingInformationInterface;

/**
 * @codeCoverageIgnoreStart
 */
class DateShippingInformation extends AbstractExtensibleModel implements DateShippingInformationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCarriergroupId()
    {
        return $this->getData(self::CARRIERGROUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCarriergroupId($carrierGroupId)
    {
        return $this->setData(self::CARRIERGROUP_ID, $carrierGroupId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCarrierId()
    {
        return $this->getData(self::CARRIER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCarrierId($carrierId)
    {
        return $this->setData(self::CARRIER_ID, $carrierId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCarrierCode()
    {
        return $this->getData(self::CARRIER_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCarrierCode($code)
    {
        return $this->setData(self::CARRIER_CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function getCartId()
    {
        return $this->getData(self::CART_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCartId($cartId)
    {
        return $this->setData(self::CART_ID, $cartId);
    }

    /**
     * Returns date
     *
     * @return string
     */
    public function getDateSelected()
    {
        return $this->getData(self::DATE_SELECTED);
    }

    /**
     * Set date
     *
     * @param string $date
     * @return $this
     */
    public function setDateSelected($date)
    {
        return $this->setData(self::DATE_SELECTED, $date);
    }

    /**
     * Get street
     *
     * @return string[]
     */
    public function getStreet()
    {
        return $this->getData(self::STREET);
    }

    /**
     * Set street
     *
     * @param string|string[] $street
     * @return $this
     */
    public function setStreet($street)
    {
        return $this->setData(self::STREET, $street);
    }

    /**
     * Returns city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * Set city
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * Returns region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->getData(self::REGION);
    }

    /**
     * Set region
     *
     * @param string $region
     * @return $this
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * Returns region id
     *
     * @return string
     */
    public function getRegionId()
    {
        return $this->getData(self::REGION_ID);
    }

    /**
     * Set region id
     *
     * @param string $regionId
     * @return $this
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * Returns country id
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * Set country id
     *
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * Returns postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->getData(self::POSTCODE);
    }

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * Returns adress
     *
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * Set address
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return $this
     */
    public function setAddress($address)
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \ShipperHQ\Calendar\Api\Data\DateShippingInformationExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
