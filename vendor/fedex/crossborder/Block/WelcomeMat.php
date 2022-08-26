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
namespace FedEx\CrossBorder\Block;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\View\Element\Template;
use FedEx\CrossBorder\Helper\Data as Helper;
use FedEx\CrossBorder\Model\WelcomeMat as WelcomeMatModel;

class WelcomeMat extends Template
{
    /**
     * @var string
     */
    protected $_content;

    /**
     * @var BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var PostHelper
     */
    protected $_postHelper;

    /**
     * @var WelcomeMatModel
     */
    protected $_welcomeMat;

    /**
     * WelcomeMat constructor.
     *
     * @param BlockFactory $blockFactory
     * @param PostHelper $postHelper
     * @param WelcomeMatModel $welcomeMat
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        BlockFactory $blockFactory,
        PostHelper $postHelper,
        WelcomeMatModel $welcomeMat,
        Template\Context $context,
        array $data = []
    ) {
        $this->_blockFactory = $blockFactory;
        $this->_postHelper = $postHelper;
        $this->_welcomeMat = $welcomeMat;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->isEnabled()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * Checks if welcome mat should be opened automatically
     *
     * @return bool
     */
    public function autoOpen()
    {
        $code = $this->getHelper()->getStoreManager()->getStore()->getCurrentCountryCode();

        return empty($code);
    }

    /**
     * Returns helper
     *
     * @return Helper
     */
    public function getHelper()
    {
        return $this->_welcomeMat->getHelper();
    }

    /**
     * Returns block html content by id
     *
     * @param mixed $blockId
     * @return string
     */
    public function getBlockContent($blockId)
    {
        $content = '';

        if (!empty($blockId)) {
            $content = $this->getLayout()->createBlock(
                'Magento\Cms\Block\Block'
            )->setBlockId(
                $blockId
            )->toHtml();
        }

        return $content;
    }

    /**
     * Returns content
     *
     * @param string|null $countryCode
     * @return string
     */
    public function getContent($countryCode = null)
    {
        if (isset($countryCode)) {
            $id = $this->_welcomeMat->getCmsBlockIdByCountry($countryCode);
            if (empty($id)) {
                $id = $this->_welcomeMat->getDefaultCmsBlockId();
            }

            return $this->getBlockContent($id);
        }

        if (!isset($this->_content)) {
            $this->_content = $this->getBlockContent(
                $this->_welcomeMat->getCmsBlockId()
            );
        }

        return $this->_content;
    }

    /**
     * Returns info blocks list
     *
     * @return array
     */
    public function getCountryData()
    {
        $result = [];
        foreach ($this->getHelper()->getAvailableCountries()->toOptionArray() as $countryCode => $countryData) {
            $currencyCode = $countryData['currency'];
            $result[$countryCode] = ['currency' => $currencyCode];
            $data = $this->_welcomeMat->getCountryData($countryCode);
            if (!empty($data['info'])) {
                $result[$countryCode]['info'] = $this->getBlockContent($data['info']);
            }
        }

        return $result;
    }

    /**
     * Returns pot url
     *
     * @return string
     */
    public function getPostUrl()
    {
        return $this->getUrl('fdxcb/welcomeMat/save');
    }

    /**
     * Returns selected country name
     *
     * @return string
     */
    public function getSelectedCountryName()
    {
        return $this->getHelper()->getAvailableCountries()->getCountryName(
            $this->getHelper()->getSelectedCountry()
        );
    }

    /**
     * Returns country post data
     *
     * @param string $code
     * @return string
     */
    public function getCountryPostData($code)
    {
        return $this->_postHelper->getPostData($this->escapeUrl($this->getPostUrl()), ['country_selector' => $code]);
    }

    /**
     * Checks if welcome mat should be shown if first-time loaded
     *
     * @return bool
     */
    public function isAutoOpen()
    {
        return $this->_welcomeMat->isAutoOpen();
    }

    /**
     * Checks if block can be shown
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_welcomeMat->isEnabled();
    }

    /**
     * Checks if should be shown welcome mat or country selector on the header
     *
     * @return bool
     */
    public function canShow()
    {
        return $this->_welcomeMat->canShow();
    }
}