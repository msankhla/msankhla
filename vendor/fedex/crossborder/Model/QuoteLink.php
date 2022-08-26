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

use FedEx\CrossBorder\Api\Data\QuoteLinkInterface;
use FedEx\CrossBorder\Model\ResourceModel\QuoteLink as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class QuoteLink extends AbstractModel implements QuoteLinkInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Returns quote id
     *
     * @return int
     */
    public function getQuoteId()
    {
        return $this->getData(static::QUOTE_ID);
    }

    /**
     * Sets quote id
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(static::QUOTE_ID, $quoteId);
    }

    /**
     * Returns FedEx order number
     *
     * @return string
     */
    public function getFxcbOrderNumber()
    {
        return $this->getData(static::FXCB_ORDER_NUMBER);
    }

    /**
     * Sets FedEx order number
     *
     * @param string $fxcbOrderNumber
     * @return $this
     */
    public function setFxcbOrderNumber($fxcbOrderNumber)
    {
        return $this->setData(static::FXCB_ORDER_NUMBER, $fxcbOrderNumber);
    }

    /**
     * Returns FedEx tracking link
     *
     * @return string
     */
    public function getTrackingLink()
    {
        return $this->getData(static::TRACKING_LINK);
    }

    /**
     * Sets FedEx tracking link
     *
     * @param string $link
     * @return $this
     */
    public function setTrackingLink($link)
    {
        return $this->setData(static::TRACKING_LINK, $link);
    }
}