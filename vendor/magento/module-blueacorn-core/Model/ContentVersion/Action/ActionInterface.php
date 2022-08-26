<?php
/**
 * @package     BlueAcorn/Core
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
declare(strict_types=1);

namespace BlueAcorn\Core\Model\ContentVersion\Action;

use BlueAcorn\Core\Model\ContentVersion\Entry;

interface ActionInterface
{
    /**
     * @param Entry $entry
     * @return bool
     */
    public function execute(Entry $entry);
}
