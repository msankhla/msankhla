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
namespace FedEx\CrossBorder\Ui\Component\PackNotification\Listing\Column;

use FedEx\CrossBorder\Helper\PackNotification as Helper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    const URL_PATH  = 'fdxcb/packNotification/';

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var array
     */
    protected $_parameters = [];

    /**
     * Actions constructor.
     *
     * @param Helper $helper
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Helper $helper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_helper = $helper;

        parent::__construct($context, $uiComponentFactory, $components, $data);
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
     * Preparing parameters
     *
     * @param array $parameters
     * @return array
     */
    public function prepareParameters($item, $parameters = [])
    {
        foreach ($this->_parameters as $name) {
            $value = (isset($item[$name]) ? $item[$name] : '');
            if (!empty($value)) {
                $parameters[$name] = $value;
            }
        }

        return $parameters;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $packId = $this->getHelper()->getCurrentPackNotificationId();
            $idName = $this->getDataByPath('config/indexField');

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$idName])) {
                    $item[$this->getData('name')]['edit'] = [
                        'href'  => $this->getHelper()->getUrl(
                            static::URL_PATH . 'editBox',
                            $this->prepareParameters($item, [
                                'id'        => $item[$idName],
                                'pack_id'   => $packId,
                            ])
                        ),
                        'label' => __('Edit'),
                        'hidden' => false,
                    ];
                }
            }
        }

        return $dataSource;
    }
}
