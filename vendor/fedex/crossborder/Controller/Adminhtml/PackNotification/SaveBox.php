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
namespace FedEx\CrossBorder\Controller\Adminhtml\PackNotification;

use FedEx\CrossBorder\Controller\Adminhtml\PackNotification as PackNotificationAction;

use FedEx\CrossBorder\Helper\PackNotification as Helper;
use FedEx\CrossBorder\Model\PackNotification\Box;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class SaveBox extends PackNotificationAction
{
    /**
     * @var array
     */
    protected $_fields = [
        Box::WIDTH,
        Box::HEIGHT,
        Box::LENGTH,
        Box::WEIGHT,
    ];

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * SaveBox constructor.
     *
     * @param LoggerInterface $logger
     * @param Helper $helper
     * @param Registry $registry
     * @param Context $context
     */
    public function __construct(
        LoggerInterface $logger,
        Helper $helper,
        Registry $registry,
        Context $context
    ) {
        $this->_logger = $logger;
        parent::__construct($helper, $registry, $context);
    }

    /**
     * Converting data
     *
     * @param array $data
     * @return array
     */
    public function convertData($data)
    {
        $result = [];
        foreach ($this->_fields as $field) {
            $result[$field] = floatval(isset($data[$field]) ? $data[$field] : 0);
        }

        return $result;
    }

    /**
     * Save action
     */
    public function execute()
    {
        if ($this->isValid()) {
            $id = $this->getRequest()->getParam('entity_id');
            $data = $this->getRequest()->getPostValue();
            if ($data) {
                $box = $this->_helper->getBox(
                    $id
                )->addItems(
                    $data['data']['links']['item'] ?? []
                )->addData(
                    $this->convertData($data)
                );

                try {
                    if (!$box->getPackNotificationId()) {
                        $packNotification = $this->_helper->getCurrentPackNotification();
                        $packNotification->addBox($box)->save();
                    } else {
                        $box->save();
                    }

                    $this->_redirect('*/*/view', ['pack_id' => $box->getPackNotificationId()]);
                    return;
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addExceptionMessage($e);
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            } else {
                $this->messageManager->addErrorMessage('No data to save');
            }

            $this->_registry->register('box_form_data', $data);
            $params = $this->_helper->getUrlParams();
            if ($id) {
                $params['id'] = $id;
            }
            $this->_forward('editBox', null, null, $params);
        } else {
            $this->_redirect('sales/order/view', ['order_id' => $this->_helper->getOrderId()]);
        }
    }
}
