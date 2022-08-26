<?php
/**
 * @package     BlueAcorn/Core
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
namespace BlueAcorn\Core\Model\ContentVersion\Action\PostProcess;

use BlueAcorn\Core\Model\ContentVersion\Action\ActionInterface;
use BlueAcorn\Core\Model\ContentVersion\Entry;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;

class Block implements ActionInterface
{
    /**
     * @var BlockCollectionFactory
     */
    private $blockCollectionFactory;

    /**
     * @var GetBlockByIdentifierInterface
     */
    private $blockByIdentifier;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @param BlockCollectionFactory $blockCollectionFactory
     * @param GetBlockByIdentifierInterface $blockByIdentifier
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        BlockCollectionFactory $blockCollectionFactory,
        GetBlockByIdentifierInterface $blockByIdentifier,
        BlockRepositoryInterface $blockRepository
    ) {
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->blockByIdentifier = $blockByIdentifier;
        $this->blockRepository = $blockRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(Entry $entry)
    {
        $block = $this->blockByIdentifier->execute($entry->getIdentifier(), 0);
        $blockContent = $block->getContent();

        $matches = [];
        if (preg_match_all('/block_id=\"(.*?)\"/', $blockContent, $matches)) {
            $collection = $this->blockCollectionFactory->create();
            $blocksIdMappings = $collection
                ->addFieldToSelect(['block_id', 'identifier'])
                ->addFieldToFilter('identifier', ['in' => $matches[1]])
                ->load();

            /** @var BlockInterface $blockIdMapping */
            foreach ($blocksIdMappings as $blockIdMapping) {
                $content = preg_replace(
                    '/block_id=\"' . $blockIdMapping->getIdentifier() . '\"/',
                    'block_id="' . $blockIdMapping->getId() . '"',
                    $block->getContent()
                );
                $block->setContent($content);
            }

            $this->blockRepository->save($block);
        }
    }
}
