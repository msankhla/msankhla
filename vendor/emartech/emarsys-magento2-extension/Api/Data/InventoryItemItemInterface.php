<?php
/**
 * Copyright ©2019 Itegration Ltd., Inc. All rights reserved.
 * See COPYING.txt for license details.
 * @author: Perencz Tamás <tamas.perencz@itegraion.com>
 */

namespace Emartech\Emarsys\Api\Data;

/**
 * Interface InventoryItemItemInterface
 */
interface InventoryItemItemInterface
{
    const SOURCE_CODE_KEY = 'source_code';
    const QUANTITY_KEY    = 'quantity';
    const IS_IN_STOCK_KEY = 'is_in_stock';

    /**
     * @return string
     */
    public function getSourceCode();

    /**
     * @param string $sourceCode
     *
     * @return $this
     */
    public function setSourceCode($sourceCode);

    /**
     * @return float
     */
    public function getQuantity();

    /**
     * @param float $quantity
     *
     * @return $this
     */
    public function setQuantity($quantity);

    /**
     * @return bool
     */
    public function getIsInStock();

    /**
     * @param int $isInStock
     *
     * @return $this
     */
    public function setIsInStock($isInStock);
}
