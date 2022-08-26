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
namespace FedEx\CrossBorder\Model;

use FedEx\CrossBorder\Api\Data\StoreConfigInterface;

class StoreConfig implements StoreConfigInterface
{
    const ID                                = 'id';
    const CODE                              = 'code';
    const WEBSITE_ID                        = 'website_id';
    const LOCALE                            = 'locale';
    const BASE_CURRENCY_CODE                = 'base_currency_code';
    const DEFAULT_DISPLAY_CURRENCY_CODE     = 'default_display_currency_code';
    const TIMEZONE                          = 'timezone';
    const WEIGHT_UNIT                       = 'weight_unit';
    const DIMENSION_UNIT                    = 'dimension_unit';
    const BASE_URL                          = 'base_url';
    const BASE_LINK_URL                     = 'base_link_url';
    const BASE_STATIC_URL                   = 'base_static_url';
    const BASE_MEDIA_URL                    = 'base_media_url';
    const SECURE_BASE_URL                   = 'secure_base_url';
    const SECURE_BASE_LINK_URL              = 'secure_base_link_url';
    const SECURE_BASE_STATIC_URL            = 'secure_base_static_url';
    const SECURE_BASE_MEDIA_URL             = 'secure_base_media_url';
    const PRODUCT_URL_SUFFIX                = 'product_url_suffix';
    const CATEGORY_URL_SUFFIX               = 'category_url_suffix';
    const PRODUCT_IDENTIFIER                = 'product_identifier';

    /**
     * @var array
     */
    protected $_data = [];

    /**
     * Returns store id
     *
     * @return int
     */
    public function getId()
    {
        return (int) $this->_data[static::ID];
    }

    /**
     * Sets store id
     *
     * @param int $value
     * @return StoreConfigInterface
     */
    public function setId($value)
    {
        $this->_data[static::ID] = (int) $value;

        return $this;
    }

    /**
     * Returns store code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_data[static::CODE];
    }

    /**
     * Sets store code
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setCode($value)
    {
        $this->_data[static::CODE] = $value;

        return $this;
    }

    /**
     * Returns website id of the store
     *
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->_data[static::WEBSITE_ID];
    }

    /**
     * Sets website id
     *
     * @param int $value
     * @return StoreConfigInterface
     */
    public function setWebsiteId($value)
    {
        $this->_data[static::WEBSITE_ID] = $value;

        return $this;
    }

    /**
     * Returns store locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->_data[static::LOCALE];
    }

    /**
     * Sets store locale
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setLocale($value)
    {
        $this->_data[static::LOCALE] = $value;

        return $this;
    }

    /**
     * Returns base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->_data[static::BASE_CURRENCY_CODE];
    }

    /**
     * Sets base currency code
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setBaseCurrencyCode($value)
    {
        $this->_data[static::BASE_CURRENCY_CODE] = $value;

        return $this;
    }

    /**
     * Returns default display currency code
     *
     * @return string
     */
    public function getDefaultDisplayCurrencyCode()
    {
        return $this->_data[static::DEFAULT_DISPLAY_CURRENCY_CODE];
    }

    /**
     * Sets default display currency code
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setDefaultDisplayCurrencyCode($value)
    {
        $this->_data[static::DEFAULT_DISPLAY_CURRENCY_CODE] = $value;

        return $this;
    }

    /**
     * Returns timezone of the store
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->_data[static::TIMEZONE];
    }

    /**
     * Sets timezone of the store
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setTimezone($value)
    {
        $this->_data[static::TIMEZONE] = $value;

        return $this;
    }

    /**
     * Return the unit of weight
     *
     * @return string
     */
    public function getWeightUnit()
    {
        return $this->_data[static::WEIGHT_UNIT];
    }

    /**
     * Sets the unit of weight
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setWeightUnit($value)
    {
        $this->_data[static::WEIGHT_UNIT] = $value;

        return $this;
    }

    /**
     * Returns the unit of dimension
     *
     * @return string
     */
    public function getDimensionUnit()
    {
        return $this->_data[static::DIMENSION_UNIT];
    }

    /**
     * Sets the unit of dimension
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setDimensionUnit($value)
    {
        $this->_data[static::DIMENSION_UNIT] = $value;

        return $this;
    }

    /**
     * Returns base URL for the store
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_data[static::BASE_URL];
    }

    /**
     * Sets base URL
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setBaseUrl($value)
    {
        $this->_data[static::BASE_URL] = $value;

        return $this;
    }

    /**
     * Returns base link URL for the store
     *
     * @return string
     */
    public function getBaseLinkUrl()
    {
        return $this->_data[static::BASE_LINK_URL];
    }

    /**
     * Sets base link URL for the store
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setBaseLinkUrl($value)
    {
        $this->_data[static::BASE_LINK_URL] = $value;

        return $this;
    }

    /**
     * Returns base static URL for the store
     *
     * @return string
     */
    public function getBaseStaticUrl()
    {
        return $this->_data[static::BASE_STATIC_URL];
    }

    /**
     * Sets base static URL for the store
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setBaseStaticUrl($value)
    {
        $this->_data[static::BASE_STATIC_URL] = $value;

        return $this;
    }

    /**
     * Returns base media URL for the store
     *
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return $this->_data[static::BASE_MEDIA_URL];
    }

    /**
     * Sets base media URL for the store
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setBaseMediaUrl($value)
    {
        $this->_data[static::BASE_MEDIA_URL] = $value;

        return $this;
    }

    /**
     * Returns secure base URL for the store
     *
     * @return string
     */
    public function getSecureBaseUrl()
    {
        return $this->_data[static::SECURE_BASE_URL];
    }

    /**
     * Sets secure base URL
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setSecureBaseUrl($value)
    {
        $this->_data[static::SECURE_BASE_URL] = $value;

        return $this;
    }

    /**
     * Returns secure base link URL for the store
     *
     * @return string
     */
    public function getSecureBaseLinkUrl()
    {
        return $this->_data[static::SECURE_BASE_LINK_URL];
    }

    /**
     * Sets secure base link URL for the store
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setSecureBaseLinkUrl($value)
    {
        $this->_data[static::SECURE_BASE_LINK_URL] = $value;

        return $this;
    }

    /**
     * Returns secure base static URL for the store
     *
     * @return string
     */
    public function getSecureBaseStaticUrl()
    {
        return $this->_data[static::SECURE_BASE_STATIC_URL];
    }

    /**
     * Sets secure base static URL for the store
     *
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setSecureBaseStaticUrl($value)
    {
        $this->_data[static::SECURE_BASE_STATIC_URL] = $value;

        return $this;
    }

    /**
     * Returns secure base media URL for the store
     *
     * @return string
     */
    public function getSecureBaseMediaUrl()
    {
        return $this->_data[static::SECURE_BASE_MEDIA_URL];
    }

    /**
     * Sets secure base media URL for the store
     *
     * @param string $secureBaseMediaUrl
     * @return StoreConfigInterface
     */
    public function setSecureBaseMediaUrl($value)
    {
        $this->_data[static::SECURE_BASE_MEDIA_URL] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductUrlSuffix()
    {
        return $this->_data[static::PRODUCT_URL_SUFFIX];
    }

    /**
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setProductUrlSuffix($value)
    {
        $this->_data[static::PRODUCT_URL_SUFFIX] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategoryUrlSuffix()
    {
        return $this->_data[static::CATEGORY_URL_SUFFIX];
    }

    /**
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setCategoryUrlSuffix($value)
    {
        $this->_data[static::CATEGORY_URL_SUFFIX] = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductIdentifier()
    {
        return $this->_data[static::PRODUCT_IDENTIFIER];
    }

    /**
     * @param string $value
     * @return StoreConfigInterface
     */
    public function setProductIdentifier($value)
    {
        $this->_data[static::PRODUCT_IDENTIFIER] = $value;

        return $this;
    }
}