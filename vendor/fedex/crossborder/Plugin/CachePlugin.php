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
namespace FedEx\CrossBorder\Plugin;

use Magento\Framework\App\Http\Context;
use FedEx\CrossBorder\Helper\Data as Helper;

class CachePlugin
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * CurrencyPlugin constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * @param Context $subject
     */
    public function beforeGetVaryString(Context $subject)
    {
        if ($subject instanceof Context) {
            $subject->setValue(
                'FEDEX_COUNTRY',
                $this->_helper->getSelectedCountry(),
                $this->_helper->getDefaultCountry()
            );
        }
    }

    /**
     * After plugin for getCacheKeyInfo()
     *
     * @param mixed $subject
     * @param mixed $value
     * @return mixed
     */
    public function afterGetCacheKeyInfo($subject, $value)
    {
        $value['fedex_selected_country'] = $this->_helper->getSelectedCountry();

        return $value;
    }
}