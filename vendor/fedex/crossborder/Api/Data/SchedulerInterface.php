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

interface SchedulerInterface
{
    const TYPE                  = 'type';
    const STATUS                = 'status';
    const JSON_DATA             = 'json_data';
    const ATTEMPTS              = 'attempts';
    const CREATED_AT            = 'created_at';
    const UPDATED_AT            = 'updated_at';

    const ATTEMPTS_LIMIT        = 10;

    const TYPE_PRODUCT          = 'product';
    const TYPE_ORDER            = 'order';

    const STATUS_NEW            = 'new';
    const STATUS_ERROR          = 'error';
    const STATUS_CANCEL         = 'cancel';
    const STATUS_IN_PROGRESS    = 'in progress';
    const STATUS_SUCCESS        = 'success';

    /**
     * Returns type
     *
     * @return string
     */
    public function getType();

    /**
     * Sets type
     *
     * @param string $value
     * @return $this
     */
    public function setType($value);

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
     * @return $this
     */
    public function setStatus($value);

    /**
     * Returns json data
     *
     * @return string
     */
    public function getJsonData();

    /**
     * Sets json data
     *
     * @param string $value
     * @return $this
     */
    public function setJsonData($value);

    /**
     * Returns number of attempts
     *
     * @return int
     */
    public function getAttempts();

    /**
     * Sets number of attempts
     *
     * @param int $value
     * @return $this
     */
    public function setAttempts($value);
}
