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
namespace FedEx\CrossBorder\Block\Adminhtml\PackNotification;

use FedEx\CrossBorder\Helper\PackNotification as Helper;
use Magento\Framework\Exception\NoSuchEntityException;

class GenericButton
{
    /**
     * @var Helper
     */
    protected $_helper;


    /**
     * GenericButton constructor.
     *
     * @param Helper $helper
     */
    public function __construct(Helper $helper) {
        $this->_helper = $helper;
    }

    /**
     * Returns helper
     *
     * @return Helper
     */
    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * Returns id value
     *
     * @return int
     */
    public function getId()
    {
        return $this->getHelper()->getId();
    }

    /**
     * Returns current order id
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getOrderId()
    {
        return $this->getHelper()->getOrderId();
    }

    /**
     * Returns current pack notification id
     *
     * @return int
     */
    public function getPackNotificationId()
    {
        return $this->getHelper()->getCurrentPackNotificationId();
    }

    /**
     * Returns url params
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getParams()
    {
        return $this->_helper->getUrlParams();
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->getHelper()->getUrl($route, $params);
    }
}
