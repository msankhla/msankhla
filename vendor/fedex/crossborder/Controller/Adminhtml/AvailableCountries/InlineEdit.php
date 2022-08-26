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
namespace FedEx\CrossBorder\Controller\Adminhtml\AvailableCountries;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use FedEx\CrossBorder\Model\AvailableCountries;

class InlineEdit extends Action
{
    const ADMIN_RESOURCE = 'FedEx_CrossBorder::available_countries';

    /**
     * @var JsonFactory
     */
    protected $_jsonFactory;

    /**
     * InlineEdit constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->_jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->_jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postData = $this->getRequest()->getParam('items', []);
            if (empty($postData)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach ($postData as $id => $data) {
                    /** @var AvailableCountries $model */
                    $model = $this->_objectManager->create(AvailableCountries::class)->load($id);
                    try {
                        $model->addData(
                            $data
                        )->save();
                    } catch (\Exception $e) {
                        $messages[] = '[Country/Territory: ' . $model->getCode() . '] ' . __('Something went wrong while saving data.');
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}