<?php
/**
 * Copyright ©2019 Itegration Ltd., Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Emartech\Emarsys\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;

class LinkField extends AbstractHelper
{

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * LinkField constructor.
     *
     * @param Context                $context
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $class
     * @param string $linkField
     *
     * @return string
     */
    public function getEntityLinkField($class, $linkField = 'entity_id')
    {
        if (class_exists(\Magento\Framework\EntityManager\MetadataPool::class)) {
            // @codingStandardsIgnoreLine
            $metadataPool = $this->objectManager->create(
                \Magento\Framework\EntityManager\MetadataPool::class
            );
            $linkField = $metadataPool->getMetadata($class)->getLinkField();
        }

        return $linkField;
    }
}
