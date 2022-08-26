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
namespace FedEx\CrossBorder\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\ModuleListInterface;

class ModuleVersion extends Field
{
    const MODULE_NAME       = 'FedEx_CrossBorder';

    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var string
     */
    protected $_template    = self::MODULE_NAME . '::system/config/module_version.phtml';

    /**
     * ModuleVersion constructor.
     *
     * @param ModuleListInterface $moduleList
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ModuleListInterface $moduleList,
        Context $context,
        array $data = []
    ) {
        $this->_moduleList = $moduleList;
        parent::__construct($context, $data);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Returns module version
     *
     * @return mixed
     */
    public function getModuleVersion()
    {
        return $this->_moduleList->getOne(self::MODULE_NAME)['setup_version'];
    }
}