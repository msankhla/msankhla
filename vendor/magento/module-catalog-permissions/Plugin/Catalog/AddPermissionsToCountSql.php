<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogPermissions\Plugin\Catalog;

use Magento\CatalogPermissions\App\ConfigInterface;
use Magento\CatalogPermissions\Model\Indexer\AbstractAction;
use Magento\CatalogPermissions\Model\Permission;
use Magento\CatalogPermissions\Helper\Data as Helper;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\ResourceConnection;

/**
 * Add catalog permissions for count query
 */
class AddPermissionsToCountSql
{
    private const TABLE_NAME = 'magento_catalogpermissions_index';

    /**
     * @var ConfigInterface
     */
    private $permissionsConfig;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ConfigInterface $permissionsConfig
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param Helper $helper
     * @param ResourceConnection $resource
     */
    public function __construct(
        ConfigInterface $permissionsConfig,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        Helper $helper,
        ResourceConnection $resource
    ) {
        $this->permissionsConfig = $permissionsConfig;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->resource = $resource;
    }

    /**
     * Apply permissions to the select object
     *
     * @param Collection $subject
     * @param Select $result
     * @return Select
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetSelectCountSql(Collection $subject, $result)
    {
        if (!$this->permissionsConfig->isEnabled() || !$this->isCategoryTableUsed($result)) {
            return $result;
        }

        $customerGroupId = (int) $this->customerSession->getCustomerGroupId();
        $fromPart = $result->getPart(Select::FROM);
        $conditions = [];
        $conditions[] = sprintf('perm.customer_group_id = %s', $customerGroupId);
        $categoryId = isset(
            $subject->getLimitationFilters()['category_id']
        ) ? (int) $subject->getLimitationFilters()['category_id'] : null;
        $storeId = (int) $subject->getStoreId();

        if (!$categoryId || $categoryId === (int) $this->storeManager->getStore($storeId)->getRootCategoryId()) {
            $conditions[] = 'perm.product_id = cat_index.product_id';
            $conditions[] = sprintf('perm.store_id = %s', $storeId);
            $joinConditions = join(' AND ', $conditions);

            $productTable = $this->resource->getTableName(self::TABLE_NAME . AbstractAction::PRODUCT_SUFFIX);

            if (!isset($fromPart['perm'])) {
                $result->joinLeft(
                    ['perm' => $productTable],
                    $joinConditions,
                    ['grant_catalog_category_view', 'grant_catalog_product_price', 'grant_checkout_items']
                );
            }
        } else {
            $tableName = $this->resource->getTableName(self::TABLE_NAME);

            $conditions[] = 'perm.category_id = cat_index.category_id';
            $websiteId = (int) $this->storeManager->getStore($storeId)->getWebsiteId();
            $conditions[] = sprintf('perm.website_id = %s', $websiteId);
            $joinConditions = join(' AND ', $conditions);

            if (!isset($fromPart['perm'])) {
                $result->joinLeft(
                    ['perm' => $tableName],
                    $joinConditions,
                    ['grant_catalog_category_view', 'grant_catalog_product_price', 'grant_checkout_items']
                );
            }
        }

        if (isset($fromPart['perm'])) {
            $fromPart['perm']['tableName'] = $tableName;
            $fromPart['perm']['joinCondition'] = $joinConditions;
            $result->setPart(Select::FROM, $fromPart);
            return $result;
        }

        if (!$this->helper->isAllowedCategoryView($storeId)) {
            $result->where('perm.grant_catalog_category_view = ?', Permission::PERMISSION_ALLOW);
        } else {
            $result->where(
                'perm.grant_catalog_category_view != ?' . ' OR perm.grant_catalog_category_view IS NULL',
                Permission::PERMISSION_DENY
            );
        }

        if (method_exists($subject, 'getLinkModel') || $subject->getFlag('is_link_collection')) {
            $result->where(
                'perm.grant_catalog_product_price != ?' . ' OR perm.grant_catalog_product_price IS NULL',
                Permission::PERMISSION_DENY
            )->where(
                'perm.grant_checkout_items != ?' . ' OR perm.grant_checkout_items IS NULL',
                Permission::PERMISSION_DENY
            );
        }

        return $result;
    }

    /**
     * Check if category tables are present in the select
     *
     * @param Select $select
     * @return bool
     */
    private function isCategoryTableUsed($select) : bool
    {
        return str_contains((string) $select, 'cat_index');
    }
}
