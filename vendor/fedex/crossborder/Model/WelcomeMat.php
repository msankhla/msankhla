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

use FedEx\CrossBorder\Helper\Data as Helper;

class WelcomeMat
{
    const CONFIG_PATH   = 'fedex_crossborder/welcome_mat/';

    /**
     * @var mixed
     */
    protected $_cmsBlockId;

    /**
     * @var array
     */
    protected $_countryData;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * WelcomeMat constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Checks if welcome mat can be shown
     *
     * @return bool
     */
    public function canShow()
    {
        return $this->isEnabled() && $this->getHelper()->getConfig(static::CONFIG_PATH . 'enable', 0);
    }

    /**
     * Returns cms block id
     *
     * @return mixed
     */
    public function getCmsBlockId()
    {
        if (!isset($this->_cmsBlockId)) {
            $this->_cmsBlockId = $this->getCmsBlockIdByCountry(
                $this->getHelper()->getSelectedCountry()
            );

            if (empty($this->_cmsBlockId)) {
                $this->_cmsBlockId = $this->getDefaultCmsBlockId();
            }
        }

        return $this->_cmsBlockId;
    }

    /**
     * Returns cms block id by country code
     *
     * @param string $countryCode
     * @return mixed|string
     */
    public function getCmsBlockIdByCountry($countryCode)
    {
        $data = $this->getCountryData($countryCode);

        return (!empty($data['cms']) ? $data['cms'] : '');
    }

    /**
     * Returns country data
     *
     * @return array|mixed
     */
    public function getCountryData($countryCode = null)
    {
        if (!isset($this->_countryData)) {
            $this->_countryData = [];
            $list = json_decode(
                $this->getHelper()->getConfig(static::CONFIG_PATH . 'country_cms_block'),
                true
            );

            if (is_array($list)) {
                foreach ($list as $item) {
                    $this->_countryData[$item['country']] = [
                        'cms'   => (isset($item['cms']) ? $item['cms'] : ''),
                        'info'  => (isset($item['info']) ? $item['info'] : ''),
                    ];
                }
            }
        }

        return (isset($countryCode) ?
            (isset($this->_countryData[$countryCode]) ? $this->_countryData[$countryCode] : '') :
            $this->_countryData
        );
    }

    /**
     * Returns default cms block id for Welcome mat
     *
     * @return mixed|null
     */
    public function getDefaultCmsBlockId()
    {
        return $this->_helper->getConfig(static::CONFIG_PATH . 'default_cms_block');
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
     * Returns info block id
     *
     * @return mixed
     */
    public function getInfoBlockId()
    {
        return $this->getInfoBlockIdByCountry(
            $this->getHelper()->getSelectedCountry()
        );
    }

    /**
     * Returns info block id by country
     *
     * @param string $countryCode
     * @return mixed|string
     */
    public function getInfoBlockIdByCountry($countryCode)
    {
        $data = $this->getCountryData($countryCode);

        return (isset($data['info']) ? $data['info'] : '');
    }

    /**
     * Checks if welcome mat should be shown if first-time loaded
     *
     * @return bool
     */
    public function isAutoOpen()
    {
        return (bool) $this->_helper->getConfig(static::CONFIG_PATH . 'auto_open');
    }

    /**
     * Checks if welcome mat is available
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->getHelper()->isEnabled();
    }
}