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
namespace FedEx\CrossBorder\Ui\Component\PackNotification;

use FedEx\CrossBorder\Helper\PackNotification as Helper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Container;

class Listing extends Container
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * Listing constructor.
     *
     * @param Helper $helper
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Helper $helper,
        ContextInterface $context,
        $components = [],
        array $data = []
    ) {
        $this->_helper = $helper;

        parent::__construct($context, $components, $data);
        $this->prepareUrls();
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
     * Preparing urls
     *
     * @return $this
     */
    public function prepareUrls()
    {
        $url = $this->getHelper()->getUrl('mui/index/render', array_merge(
            $this->getHelper()->getUrlParams(),
            ['id' => $this->getHelper()->getId()]
        ));
        $this->_data['config']['render_url'] = $url;
        $this->_data['config']['update_url'] = $url;

        return $this;
    }

}
