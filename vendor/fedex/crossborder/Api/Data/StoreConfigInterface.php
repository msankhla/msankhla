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
namespace FedEx\CrossBorder\Api\Data;

interface StoreConfigInterface
{
    /**
     * Returns store id
     *
     * @return int
     */
    public function getId();

    /**
     * Sets store id
     *
     * @param int $value
     * @return StoreConfigInterface
     */
    public function setId($value);

    /**
     * Returns store code
     *
     * @return string
     */
    public function getCode();

    /**
     * Sets store code
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setCode($value);

    /**
     * Returns website id of the store
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Sets website id
     *
     * @param int $value
     * @return StoreConfigInterface
     */
    public function setWebsiteId($value);

    /**
     * Returns store locale
     *
     * @return string
     */
    public function getLocale();

    /**
     * Sets store locale
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setLocale($value);

    /**
     * Returns base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode();

    /**
     * Sets base currency code
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setBaseCurrencyCode($value);

    /**
     * Returns default display currency code
     *
     * @return string
     */
    public function getDefaultDisplayCurrencyCode();

    /**
     * Sets default display currency code
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setDefaultDisplayCurrencyCode($value);

    /**
     * Returns timezone of the store
     *
     * @return string
     */
    public function getTimezone();

    /**
     * Sets timezone of the store
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setTimezone($value);

    /**
     * Return the unit of weight
     *
     * @return string
     */
    public function getWeightUnit();

    /**
     * Sets the unit of weight
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setWeightUnit($value);

    /**
     * Returns the unit of dimension
     *
     * @return string
     */
    public function getDimensionUnit();

    /**
     * Sets the unit of dimension
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setDimensionUnit($value);

    /**
     * Returns base URL for the store
     *
     * @return string
     */
    public function getBaseUrl();

    /**
     * Sets base URL
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setBaseUrl($value);

    /**
     * Returns base link URL for the store
     *
     * @return string
     */
    public function getBaseLinkUrl();

    /**
     * Sets base link URL for the store
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setBaseLinkUrl($value);

    /**
     * Returns base static URL for the store
     *
     * @return string
     */
    public function getBaseStaticUrl();

    /**
     * Sets base static URL for the store
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setBaseStaticUrl($value);

    /**
     * Returns base media URL for the store
     *
     * @return string
     */
    public function getBaseMediaUrl();

    /**
     * Sets base media URL for the store
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setBaseMediaUrl($value);

    /**
     * Returns secure base URL for the store
     *
     * @return string
     */
    public function getSecureBaseUrl();

    /**
     * Sets secure base URL
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setSecureBaseUrl($value);

    /**
     * Returns secure base link URL for the store
     *
     * @return string
     */
    public function getSecureBaseLinkUrl();

    /**
     * Sets secure base link URL for the store
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setSecureBaseLinkUrl($value);

    /**
     * Returns secure base static URL for the store
     *
     * @return string
     */
    public function getSecureBaseStaticUrl();

    /**
     * Sets secure base static URL for the store
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setSecureBaseStaticUrl($value);

    /**
     * Returns secure base media URL for the store
     *
     * @return string
     */
    public function getSecureBaseMediaUrl();

    /**
     * Sets secure base media URL for the store
     *
     * @param string $secureBaseMediaUrl
     * @return StoreConfigInterface
     */
    public function setSecureBaseMediaUrl($value);

    /**
     * @return string
     */
    public function getProductUrlSuffix();

    /**
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setProductUrlSuffix($value);

    /**
     * @return string
     */
    public function getCategoryUrlSuffix();

    /**
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setCategoryUrlSuffix($value);

    /**
     * @return string
     */
    public function getProductIdentifier();

    /**
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setProductIdentifier($value);
}