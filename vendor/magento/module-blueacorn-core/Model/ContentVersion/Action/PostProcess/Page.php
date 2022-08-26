<?php
/**
 * @package     BlueAcorn/CMSBlocks
 * @version     1.0.0
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
namespace BlueAcorn\Core\Model\ContentVersion\Action\PostProcess;

use BlueAcorn\Core\Model\ContentVersion\Action\ActionInterface;
use BlueAcorn\Core\Model\ContentVersion\Entry;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;

class Page implements ActionInterface
{
    /**
     * @var BlockCollectionFactory
     */
    private $blockCollectionFactory;

    /**
     * @var GetPageByIdentifierInterface
     */
    private $pageByIdentifier;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * PageInstallPostProcessor constructor.
     * @param BlockCollectionFactory $blockCollectionFactory
     * @param GetPageByIdentifierInterface $pageByIdentifier
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(
        BlockCollectionFactory $blockCollectionFactory,
        GetPageByIdentifierInterface $pageByIdentifier,
        PageRepositoryInterface $pageRepository
    ) {
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->pageByIdentifier = $pageByIdentifier;
        $this->pageRepository = $pageRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(Entry $entry)
    {
        $page = $this->pageByIdentifier->execute($entry->getIdentifier(), 0);
        $pageContent = $page->getContent();

        $matches = [];
        if (preg_match_all('/block_id=\"(.*?)\"/', $pageContent, $matches)) {
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
                    $page->getContent()
                );
                $page->setContent($content);
            }

            $this->pageRepository->save($page);
        }
    }
}
