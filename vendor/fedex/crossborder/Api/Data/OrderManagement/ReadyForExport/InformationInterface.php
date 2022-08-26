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

interface InformationInterface
{
    const DOCUMENTS                 = 'documents';
    const STATUS                    = 'status';
    const ID_PACK_NOTIFICATION      = 'id_pack_notification';
    const RETAILER_ORDER_NUMBER     = 'retailer_order_number';
    const DOCUMENTS_URL             = 'documents_url';
    const CANCEL_URL                = 'cancel_url';
    const TRACKING_NUMBER           = 'tracking_number';
    const IDORDER                   = 'idorder';

    /**
     * Returns data
     *
     * @return \FedEx\CrossBorder\Api\Data\OrderManagement\ReadyForExport\DocumentInterface[]
     */
    public function getDocuments();

    /**
     * Sets data
     *
     * @param \FedEx\CrossBorder\Api\Data\OrderManagement\ReadyForExport\DocumentInterface[] $value
     * @return InformationInterface
     */
    public function setDocuments($value);

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
     * @return InformationInterface
     */
    public function setStatus($value);

    /**
     * Returns pack notification id
     *
     * @return string
     */
    public function getIdPackNotification();

    /**
     * Sets pack notification id
     *
     * @param string $value
     * @return InformationInterface
     */
    public function setIdPackNotification($value);

    /**
     * Returns retailer order number
     *
     * @return string
     */
    public function getRetailerOrderNumber();

    /**
     * Sets retailer order number
     *
     * @param string $value
     * @return InformationInterface
     */
    public function setRetailerOrderNumber($value);

    /**
     * Returns documents url
     *
     * @return string
     */
    public function getDocumentsUrl();

    /**
     * Sets documents url
     *
     * @param string $value
     * @return InformationInterface
     */
    public function setDocumentsUrl($value);

    /**
     * Returns cancel url
     *
     * @return string
     */
    public function getCancelUrl();

    /**
     * Sets cancel url
     *
     * @param string $value
     * @return InformationInterface
     */
    public function setCancelUrl($value);

    /**
     * Returns tracking number
     *
     * @return string
     */
    public function getTrackingNumber();

    /**
     * Sets tracking number
     *
     * @param string $value
     * @return InformationInterface
     */
    public function setTrackingNumber($value);

    /**
     * Returns order id
     *
     * @return string
     */
    public function getIdorder();

    /**
     * Sets order_id
     *
     * @param string $value
     * @return InformationInterface
     */
    public function setIdorder($value);
}