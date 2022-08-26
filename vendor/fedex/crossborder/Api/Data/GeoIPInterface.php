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

interface GeoIPInterface
{
    const IP                = 'ip';
    const COUNTRY_CODE      = 'country_code';
    const COUNTRY_CURRENCY  = 'country_currency';

    /**
     * Returns id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Sets id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Returns ip
     *
     * @return string
     */
    public function getIp();

    /**
     * Sets ip
     *
     * @param string $ip
     * @return $this
     */
    public function setIp($ip);

    /**
     * Returns country code
     *
     * @return string
     */
    public function getCountryCode();

    /**
     * Sets country code
     *
     * @param string $code
     * @return $this
     */
    public function setCountryCode($code);

    /**
     * Returns country default currency
     *
     * @return string
     */
    public function getCountryCurrency();

    /**
     * Sets country default currency
     *
     * @param string $code
     * @return $this
     */
    public function setCountryCurrency($code);
}
