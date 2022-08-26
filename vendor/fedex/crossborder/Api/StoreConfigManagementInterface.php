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
namespace FedEx\CrossBorder\Api;

/**
 * @api
 */
interface StoreConfigManagementInterface
{
    /**
     * Returns store configs
     *
     * @param string[] $storeCodes
     * @return \FedEx\CrossBorder\Api\Data\StoreConfigInterface[]
     */
    public function getStoreConfigs(array $storeCodes = null);
}
