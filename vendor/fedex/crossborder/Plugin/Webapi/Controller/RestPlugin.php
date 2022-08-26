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
namespace FedEx\CrossBorder\Plugin\Webapi\Controller;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\ErrorProcessor;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Framework\App\RequestInterface;
use Magento\Webapi\Controller\Rest;
use FedEx\CrossBorder\Helper\Data as Helper;

class RestPlugin
{
    /**
     * @var ErrorProcessor
     */
    protected $_errorProcessor;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var array
     */
    protected $_patternsList = [
        '/V1\/(guest-)?carts\/[^\/]+\/shipping-information/',
        '/V1\/(guest-)?carts\/[^\/]+\/payment-information/',
    ];

    /**
     * @var RestResponse
     */
    protected $_response;

    /**
     * RestPlugin constructor.
     *
     * @param ErrorProcessor $errorProcessor
     * @param Helper $helper
     * @param RestResponse $response
     */
    public function __construct(
        ErrorProcessor $errorProcessor,
        Helper $helper,
        RestResponse $response
    ) {
        $this->_errorProcessor = $errorProcessor;
        $this->_helper = $helper;
        $this->_response = $response;
    }

    /**
     * Checks if path valid
     *
     * @param string $path
     * @return bool
     */
    public function valid($path)
    {
        foreach ($this->_patternsList as $pattern) {
            if (preg_match($pattern, $path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Around dispatch plugin
     *
     * @param Rest $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function aroundDispatch(
        Rest $subject,
        callable $proceed,
        RequestInterface $request
    ) {
        if ($this->_helper->isInternational() && !$this->valid($request->getPathInfo())) {
            $maskedException = $this->_errorProcessor->maskException(
                new LocalizedException(__('One-page checkout is not available for international shipping.'))
            );
            return $this->_response->setException($maskedException);
        }

        return $proceed($request);
    }
}