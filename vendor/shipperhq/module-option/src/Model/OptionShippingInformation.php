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
 * @package ShipperHQ_Option
 * @copyright Copyright (c) 2017 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\Option\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use ShipperHQ\Option\Api\Data\OptionShippingInformationInterface;

/**
 * @codeCoverageIgnoreStart
 */
class OptionShippingInformation extends AbstractExtensibleModel implements OptionShippingInformationInterface
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
     * Returns destination type
     *
     * @return string
     */
    public function getDestinationType()
    {
        return $this->getData(self::DESTINATION_TYPE);
    }

    /**
     * Set destination type
     *
     * @param string $destinationType
     * @return $this
     */
    public function setDestinationType($destinationType)
    {
        return $this->setData(self::DESTINATION_TYPE, $destinationType);
    }

    /**
     * Returns liftgate required
     *
     * @return string
     */
    public function getLiftgateRequired()
    {
        return $this->getData(self::LIFTGATE_REQUIRED);
    }

    /**
     * Set liftgate required
     *
     * @param string $liftgateRequired
     * @return $this
     */
    public function setLiftgateRequired($liftgateRequired)
    {
        return $this->setData(self::LIFTGATE_REQUIRED, $liftgateRequired);
    }

    /**
     * Returns notify required
     *
     * @return string
     */
    public function getNotifyRequired()
    {
        return $this->getData(self::NOTIFY_REQUIRED);
    }

    /**
     * Set notify required
     *
     * @param string $notifyRequired
     * @return $this
     */
    public function setNotifyRequired($notifyRequired)
    {
        return $this->setData(self::NOTIFY_REQUIRED, $notifyRequired);
    }

    /**
     * Returns limited delivery
     *
     * @return string
     */
    public function getLimitedDelivery()
    {
        return $this->getData(self::LIMITED_DELIVERY);
    }

    /**
     * Set limited delivery
     *
     * @param string $limitedDelivery
     * @return $this
     */
    public function setLimitedDelivery($limitedDelivery)
    {
        return $this->setData(self::LIMITED_DELIVERY, $limitedDelivery);
    }

    /**
     * Returns inside delivery
     *
     * @return string
     */
    public function getInsideDelivery()
    {
        return $this->getData(self::INSIDE_DELIVERY);
    }

    /**
     * Set inside delivery
     *
     * @param string $insideDelivery
     * @return $this
     */
    public function setInsideDelivery($insideDelivery)
    {
        return $this->setData(self::INSIDE_DELIVERY, $insideDelivery);
    }

    /**
     * Returns customer carrier
     *
     * @return string
     */
    public function getCustomerCarrier()
    {
        return $this->getData(self::CUSTOMER_CARRIER);
    }

    /**
     * Set customer carrier
     *
     * @param string $customerCarrier
     * @return $this
     */
    public function setCustomerCarrier($customerCarrier)
    {
        return $this->setData(self::CUSTOMER_CARRIER, $customerCarrier);

    }

    /**
     * Returns customer carrier ph
     *
     * @return string
     */
    public function getCustomerCarrierPh()
    {
        return $this->getData(self::CUSTOMER_CARRIER_PH);
    }
    /**
     * Set customer carrier ph
     *
     * @param string $customerCarrierPh
     * @return $this
     */
    public function setCustomerCarrierPh($customerCarrierPh)
    {
        return $this->setData(self::CUSTOMER_CARRIER_PH, $customerCarrierPh);

    }

    /**
     * Returns customer carrier account number
     *
     * @return string
     */
    public function getCustomerCarrierAccount()
    {
        return $this->getData(self::CUSTOMER_CARRIER_ACCOUNT);
    }

    /**
     * Set customer carrier account number
     *
     * @param string $customerCarrierAccount
     * @return $this
     */
    public function setCustomerCarrierAccount($customerCarrierAccount)
    {
        return $this->setData(self::CUSTOMER_CARRIER_ACCOUNT, $customerCarrierAccount);

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
        \ShipperHQ\Option\Api\Data\OptionShippingInformationExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
