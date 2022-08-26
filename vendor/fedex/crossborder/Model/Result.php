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

use FedEx\CrossBorder\Api\Data\ResultInterface;

class Result implements ResultInterface
{
    /**
     * @var string
     */
    protected $_status      = self::STATUS_SUCCESS;

    /**
     * @var string
     */
    protected $_message;

    /**
     * Returns status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Sets status
     *
     * @param string $value
     * @return ResultInterface
     */
    public function setStatus($value)
    {
        $this->_status = $value;

        return $this;
    }

    /**
     * Returns message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Sets message
     *
     * @param string $value
     * @return ResultInterface
     */
    public function setMessage($value)
    {
        $this->_message = $value;

        return $this;
    }

    /**
     * Add error message
     *
     * @param string $message
     * @return $this
     */
    public function addErrorMessage($message)
    {
        $this->setStatus(
            static::STATUS_ERROR
        )->setMessage(
            $message
        );

        return $this;
    }

    /**
     * Reset data
     *
     * @return $this
     */
    public function reset()
    {
        $this->_status = static::STATUS_SUCCESS;
        $this->_message = '';

        return $this;
    }
}
