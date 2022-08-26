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

interface AvailableCountriesInterface
{
    const CODE      = 'code';
    const CURRENCY  = 'currency';
    const NAME      = 'name';

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
     * Returns country code
     *
     * @return string
     */
    public function getCode();

    /**
     * Sets country code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Returns country default currency
     *
     * @return string
     */
    public function getCurrency();

    /**
     * Sets country default currency
     *
     * @param string $code
     * @return $this
     */
    public function setCurrency($code);

    /**
     * Returns country name
     *
     * @return string
     */
    public function getName();

    /**
     * Sets country name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);
}
