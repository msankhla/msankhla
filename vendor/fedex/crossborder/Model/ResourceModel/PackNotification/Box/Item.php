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
namespace FedEx\CrossBorder\Model\ResourceModel\PackNotification\Box;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Item extends AbstractDb
{
    const TABLE_NAME    = 'fdxcb_pack_notification_box_item';
    const ERROR_BOX_ID  = 'Box ID not set';

    protected function _construct()
    {
        $this->_init(static::TABLE_NAME, 'entity_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getBoxId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(static::ERROR_BOX_ID)
            );
        }

        return parent::_beforeSave($object);
    }
}