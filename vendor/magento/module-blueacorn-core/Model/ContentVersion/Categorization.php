<?php
/**
 * @package     BlueAcorn/Core
 * @author      Blue Acorn iCi. <code@blueacorn.com>
 * @copyright   Copyright © Blue Acorn iCi. All Rights Reserved.
 */
declare(strict_types=1);

namespace BlueAcorn\Core\Model\ContentVersion;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Categorization extends DataObject
{
    const IS_NEW = 'is_new';
    const IS_UPDATE = 'is_update';

    const TYPE = 'type';
    const ITEMS = 'items';

    /**
     * Valid categorization types
     * @var array
     */
    private $validTypes = [
        self::IS_NEW,
        self::IS_UPDATE
    ];

    /**
     * @param string $type
     * @return Categorization
     * @throws LocalizedException
     */
    public function setType(string $type): self
    {
        if (!in_array($type, $this->validTypes)) {
            throw new LocalizedException(__('%type is not a valid type', ['type' => $type]));
        }

        return $this->setData(self::TYPE, $type);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @param array $items
     * @return Categorization
     */
    public function setItems(array $items): self
    {
        return $this->setData(self::ITEMS, $items);
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->getData(self::ITEMS) ?? [];
    }
}
