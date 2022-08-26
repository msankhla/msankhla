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

use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Action extends \Magento\Ui\Component\Action
{
    /**
     * Action constructor.
     *
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param null $actions
     * @param null $urlPath
     */
    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = [],
        $actions = null,
        $urlPath = null
    ) {
        parent::__construct($context, $components, $data, $actions);
    }

    /**
     * Returns param value
     *
     * @param string $name
     * @return mixed
     */
    public function getRequestParam($name)
    {
        return $this->getContext()->getRequestParam($name);
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
     * Preparing data
     */
    public function prepare()
    {
        $config = $this->getConfiguration();
        if (isset($config['urlPath'])) {
            $params = (isset($config['urlParams']) ? (array) $config['urlParams'] : []);
            foreach ($params as $name => &$value) {
                if ($value == '*') {
                    $value = $this->getRequestParam($name);
                }
            }
            $config['url'] = $this->getUrl(
                $config['urlPath'],
                $params
            );

            $this->setConfig($config);
        }

        parent::prepare();
    }
}