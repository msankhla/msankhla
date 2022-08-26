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
namespace FedEx\CrossBorder\Plugin\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product\Save;

class SavePlugin
{
    /**
     * @param Save $subject
     */
    public function beforeExecute(Save $subject) {
        $data = $subject->getRequest()->getPostValue('product');
        if (empty($data['fdx_carton'])) {
            $data['fdx_carton'] = [];
        }

        $subject->getRequest()->setPostValue('product', $data);
    }
}