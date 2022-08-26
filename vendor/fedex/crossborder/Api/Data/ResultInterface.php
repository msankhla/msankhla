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
namespace FedEx\CrossBorder\Api\Data;

interface ResultInterface
{
    const STATUS                = 'status';
    const MESSAGE               = 'message';

    const STATUS_SUCCESS    = 'success';
    const STATUS_ERROR      = 'error';

    /**
     * Returns status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Sets status
     *
     * @param string $value
     * @return ResultInterface
     */
    public function setStatus($value);

    /**
     * Returns message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Sets message
     *
     * @param string $value
     * @return ResultInterface
     */
    public function setMessage($value);
}
