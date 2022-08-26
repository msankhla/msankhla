<?php
/**
 * @package     BlueAcorn/Core
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
namespace BlueAcorn\Core\Model\ContentVersion;

use Magento\PageBuilder\Api\Data\TemplateSearchResultsInterfaceFactory;

/**
 * Factory class for @see \Magento\Framework\Api\SearchResults
 */
class SearchResultsFactory extends TemplateSearchResultsInterfaceFactory
{
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = '\\Magento\\Framework\\Api\\SearchResults'
    ) {
        parent::__construct(
            $objectManager,
            $instanceName
        );
    }
}
