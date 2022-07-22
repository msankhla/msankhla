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
 * @category  ShipperHQ
 * @package   ShipperHQ_Calendar
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\Calendar\Api\Data;

/**
 * Interface ShippingInformationInterface
 * @api
 */
interface DateShippingInformationInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{

    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const CARRIERGROUP_ID = 'carriergroup_id';

    const CARRIER_CODE = 'carrier_code';

    const CARRIER_ID = 'carrier_id';

    const CART_ID = 'cart_id';

    const DATE_SELECTED = 'date_selected';

    const STREET = 'street';

    const CITY = 'city';

    const REGION = 'region';

    const REGION_ID = 'region_id';

    const COUNTRY_ID = 'country_id';

    const POSTCODE = 'postcode';

    const ADDRESS = 'address';

    /**
     * Returns carrier group ID
     *
     * @return string
     */
    public function getCarriergroupId();

    /**
     * Set carrier group ID
     *
     * @param string $carriergroup_id
     * @return $this
     */
    public function setCarriergroupId($carriergroup_id);

    /**
     * Returns carrier code
     *
     * @return string
     */
    public function getCarrierCode();

    /**
     * Set carrier code
     *
     * @param string $carrierCode
     * @return $this
     */
    public function setCarrierCode($carrierCode);

    /**
     * Returns carrier id
     *
     * @return string
     */
    public function getCarrierId();

    /**
     * Set carrier id
     *
     * @param string $carrierId
     * @return $this
     */
    public function setCarrierId($carrierId);

    /**
     * Returns cart Id
     *
     * @return string
     */
    public function getCartId();

    /**
     * Set cart ID
     *
     * @param string $cartId
     * @return $this
     */
    public function setCartId($cartId);

    /**
     * Returns date
     *
     * @return string
     */
    public function getDateSelected();

    /**
     * Set date
     *
     * @param string $date
     * @return $this
     */
    public function setDateSelected($date);

    /**
     * Get street
     *
     * @return string[]
     */
    public function getStreet();

    /**
     * Set street
     *
     * @param string|string[] $street
     * @return $this
     */
    public function setStreet($street);

    /**
     * Returns city
     *
     * @return string
     */
    public function getCity();

    /**
     * Set city
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * Returns region
     *
     * @return string
     */
    public function getRegion();

    /**
     * Set region
     *
     * @param string $region
     * @return $this
     */
    public function setRegion($region);

    /**
     * Returns region id
     *
     * @return string
     */
    public function getRegionId();

    /**
     * Set region id
     *
     * @param string $regionId
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * Returns country id
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Set country id
     *
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId);

    /**
     * Returns postcode
     *
     * @return string
     */
    public function getPostcode();

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode);

    /**
     * Returns adress
     *
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getAddress();

    /**
     * Set address
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return $this
     */
    public function setAddress($address);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \ShipperHQ\Calendar\Api\Data\DateShippingInformationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \ShipperHQ\Calendar\Api\Data\DateShippingInformationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \ShipperHQ\Calendar\Api\Data\DateShippingInformationExtensionInterface $extensionAttributes
    );
}
