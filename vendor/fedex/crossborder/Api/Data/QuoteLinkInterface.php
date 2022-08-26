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

interface QuoteLinkInterface
{
    const QUOTE_ID          = 'quote_id';
    const FXCB_ORDER_NUMBER = 'fxcb_order_number';
    const TRACKING_LINK     = 'tracking_link';

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
     * Returns quote id
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Sets quote id
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * Returns FedEx order number
     *
     * @return string
     */
    public function getFxcbOrderNumber();

    /**
     * Sets FedEx order number
     *
     * @param string $fxcbOrderNumber
     * @return $this
     */
    public function setFxcbOrderNumber($fxcbOrderNumber);

    /**
     * Returns FedEx tracking link
     *
     * @return string
     */
    public function getTrackingLink();

    /**
     * Sets FedEx tracking link
     *
     * @param string $link
     * @return $this
     */
    public function setTrackingLink($link);
}
