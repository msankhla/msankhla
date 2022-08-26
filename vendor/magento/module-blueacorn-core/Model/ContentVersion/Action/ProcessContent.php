<?php
/**
 * @package     BlueAcorn/Core
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
declare(strict_types=1);

namespace BlueAcorn\Core\Model\ContentVersion\Action;

use BlueAcorn\Core\Model\ContentVersion\Categorization;
use BlueAcorn\Core\Model\ContentVersion\Categorization\Resolver as CategorizationResolver;
use BlueAcorn\Core\Model\ContentVersion\Entry\Resolver as EntryResolver;

class ProcessContent
{
    /**
     * @var EntryResolver
     */
    private $entryResolver;

    /**
     * @var CategorizationResolver
     */
    private $categorizationResolver;

    /**
     * @var ActionInterface
     */
    private $saveAction;

    /**
     * @var ActionInterface
     */
    private $postProcessAction;

    /**
     * @param EntryResolver $entryResolver
     * @param CategorizationResolver $categorizationResolver
     * @param ActionInterface $saveAction
     * @param ActionInterface $postProcessAction
     */
    public function __construct(
        EntryResolver $entryResolver,
        CategorizationResolver $categorizationResolver,
        ActionInterface $saveAction,
        ActionInterface $postProcessAction
    ) {
        $this->entryResolver = $entryResolver;
        $this->categorizationResolver = $categorizationResolver;
        $this->saveAction = $saveAction;
        $this->postProcessAction = $postProcessAction;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $entries = $this->entryResolver->execute();
        $categories = $this->categorizationResolver->execute($entries);

        /** @var Categorization $category */
        foreach ($categories as $category) {
            foreach ($category->getItems() as $entry) {
                $this->saveAction->execute($entry);
                $this->postProcessAction->execute($entry);
            }
        }
    }
}
