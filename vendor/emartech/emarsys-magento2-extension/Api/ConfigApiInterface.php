<?php

namespace Emartech\Emarsys\Api;

use Emartech\Emarsys\Api\Data\ConfigInterface;

interface ConfigApiInterface
{
    /**
     * @param int                                        $websiteId
     * @param \Emartech\Emarsys\Api\Data\ConfigInterface $config
     *
     * @return \Emartech\Emarsys\Api\Data\StatusResponseInterface
     */
    public function set($websiteId, ConfigInterface $config);

    /**
     * @param string   $type
     * @param int      $websiteId
     * @param string[] $codes
     *
     * @return \Emartech\Emarsys\Api\Data\StatusResponseInterface
     */
    public function setAttributes($type, $websiteId, $codes);
}
