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
namespace FedEx\CrossBorder\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class OrderLink extends AbstractDb
{
    const TABLE_NAME = 'fdxcb_sales_order';

    /**
     * QuoteLink constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init(static::TABLE_NAME, 'entity_id');
    }

    /**
     * Perform actions after object save
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(
        AbstractModel $object
    ) {
        if ($object->hasOriginalShippingAddress()) {
            $object->getOriginalShippingAddress()->save();
        }

        return $this;
    }
}