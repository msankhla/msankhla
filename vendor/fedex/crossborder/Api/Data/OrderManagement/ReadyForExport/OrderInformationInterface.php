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
namespace FedEx\CrossBorder\Api\Data\OrderManagement\ReadyForExport;

interface OrderInformationInterface
{
    const ID                        = 'id';
    const CREATED_AT                = 'created_at';
    const TYPE                      = 'type';
    const INFORMATION               = 'information';
    const STATUS                    = 'status';

    /**
     * Returns id
     *
     * @return string
     */
    public function getId();

    /**
     * Sets id
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setId($value);

    /**
     * Returns date created
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Sets date created
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setCreatedAt($value);

    /**
     * Returns status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Sets status
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setStatus($value);

    /**
     * Returns type
     *
     * @return string
     */
    public function getType();

    /**
     * Sets type
     *
     * @param string $value
     * @return OrderInformationInterface
     */
    public function setType($value);

    /**
     * Returns information
     *
     * @return \FedEx\CrossBorder\Api\Data\OrderManagement\ReadyForExport\InformationInterface
     */
    public function getInformation();

    /**
     * Sets information
     *
     * @param \FedEx\CrossBorder\Api\Data\OrderManagement\ReadyForExport\InformationInterface $value
     * @return OrderInformationInterface
     */
    public function setInformation($value);
}