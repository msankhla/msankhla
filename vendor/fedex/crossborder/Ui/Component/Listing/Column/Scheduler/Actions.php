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
namespace FedEx\CrossBorder\Ui\Component\Listing\Column\Scheduler;

use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    const URL_PATH  = 'fdxcb/scheduler/';

    /**
     * @var array
     */
    protected $_parameters = [];

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
     * Returns url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route, $params = [])
    {
        return $this->getContext()->getUrl($route, $params);
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
            $idName = $this->getDataByPath('config/indexField');

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$idName])) {
                    $item[$this->getData('name')]['view'] = [
                        'href'  => $this->getUrl(
                            static::URL_PATH . 'view',
                            $this->prepareParameters($item, [
                                'id'        => $item[$idName],
                            ])
                        ),
                        'label' => __('Details'),
                        'hidden' => false,
                    ];
                }
            }
        }

        return $dataSource;
    }
}