<?php

namespace Emartech\Emarsys\Model\Data;

use Emartech\Emarsys\Api\Data\StatusResponseInterface;

class StatusResponse extends ErrorResponse implements StatusResponseInterface
{
    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS_KEY);
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS_KEY, $status);
        return $this;
    }
}
