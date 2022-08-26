<?php
/**
 * FedEx Cross Border component
 *
 * @category    FedEx
 * @package     FedEx_CrossBorder
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\CrossBorder\Setup;

use FedEx\CrossBorder\Model\ResourceModel\AvailableCountries;
use FedEx\CrossBorder\Model\ResourceModel\OrderLink;
use FedEx\CrossBorder\Model\ResourceModel\OrderLink\Address;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box;
use FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box\Item as BoxItem;
use FedEx\CrossBorder\Model\ResourceModel\QuoteLink;
use FedEx\CrossBorder\Model\ResourceModel\Scheduler;
use FedEx\CrossBorder\Model\ResourceModel\TaxRate;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Module\Setup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @var SchemaSetupInterface
     */
    protected $_setup;

    /**
     * @var EavSetupFactory
     */
    protected $_eavSetupFactory;

    /**
     * @var array
     */
    protected $_info    = [
        'attributes'    => [
            'fdx_country_of_origin',
            'fdx_hs_code',
            'fdx_eccn',
            'fdx_haz_flag',
            'fdx_product_type',
            'fdx_license_flag',
            'fdx_import_flag',
            'fdx_multi_country_of_origin',
            'fdx_callback_url',
            'fdx_carton',
        ],
        'blocks'        => [
            'welcome-mat-greeting',
            'welcome-mat-info-default',
        ],
        'configs'       => [
            'general/locale/dimension_unit',
            'carriers/fdxcb/%',
            'currency/fedex_crossborder/%',
            'fedex_crossborder/%',
            'payment/fdxcb/%',
            'shipping/origin/name',
            'shipping/origin/phone',
        ],
        'tables'        => [
            AvailableCountries::TABLE_NAME,
            'fdxcb_geo_ip',
            BoxItem::TABLE_NAME,
            Box::TABLE_NAME,
            PackNotification::TABLE_NAME,
            QuoteLink::TABLE_NAME,
            Address::TABLE_NAME,
            OrderLink::TABLE_NAME,
            Scheduler::TABLE_NAME,
            TaxRate::TABLE_NAME,
        ],
    ];

    /**
     * Uninstall constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Srop tables
     *
     * @return $this
     */
    public function dropTables()
    {
        foreach ($this->_info['tables'] as $table) {
            $this->_setup->getConnection()->dropTable($table);
        }

        return $this;
    }

    /**
     * Removing attributes
     *
     * @return $this
     */
    public function removeAttributes()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create();

        foreach ($this->_info['attributes'] as $attributeCode) {
            $eavSetup->removeAttribute(Product::ENTITY, $attributeCode);
        }

        return $this;
    }

    /**
     * Removing cms blocks
     *
     * @return $this
     */
    public function removeBlocks()
    {
        $configTable = $this->_setup->getTable('cms_block');
        $this->_setup->getConnection()->delete(
            $configTable,
            sprintf(
                '`identifier` in ("%s")',
                implode('", "', $this->_info['blocks'])
            )
        );

        return $this;
    }

    /**
     * Removing configs
     *
     * @return $this
     */
    public function removeConfigs()
    {
        $configTable = $this->_setup->getTable('core_config_data');
        foreach ($this->_info['configs'] as $path) {
            $this->_setup->getConnection()->delete(
                $configTable,
                sprintf(
                    '`path` like "%s"',
                    $path
                )
            );
        }


        return $this;
    }

    /**
     * Removing order statuses
     *
     * @return $this
     */
    public function removeOrderStatuses()
    {
        $configTable = $this->_setup->getTable('sales_order_status');
        $this->_setup->getConnection()->delete(
            $configTable,
            '`status` like "fdxcb_%"'
        );

        return $this;
    }

    /**
     * Remove data that was created during module installation.
     *
     * @param SchemaSetupInterface|Setup $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->_setup = $setup;

        $this->dropTables(
        )->removeAttributes(
        )->removeBlocks(
        )->removeConfigs(
        )->removeOrderStatuses(
        );
    }
}
