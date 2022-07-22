<?php
/**
 * @package     BlueAcorn/Core
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
declare(strict_types=1);

namespace BlueAcorn\Core\Model\ContentVersion\Action;

use BlueAcorn\Core\Model\ContentVersion\Entry;

class ActionPool implements ActionInterface
{
    /**
     * @var ActionInterface[]
     */
    private $processors = [];

    /**
     * @param ActionInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }

    /**
     * @inheritDoc
     */
    public function execute(Entry $entry)
    {
        if (array_key_exists($entry->getType(), $this->processors)) {
            return $this->processors[$entry->getType()]->execute($entry);
        } else {
            return false;
        }
    }
}
