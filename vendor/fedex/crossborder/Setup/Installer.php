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

use Magento\Framework\Setup;
use FedEx\Installer\Model\Product\Attribute;
use FedEx\Installer\Model\Cms\Block;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * Setup class for product attributes
     *
     * @var Attribute
     */
    protected $_attributeSetup;

    /**
     * Setup class for cms blocks
     *
     * @var Block
     */
    protected $_blockSetup;

    /**
     * Installer constructor.
     *
     * @param Attribute $attributeSetup
     * @param Block $blockSetup
     */
    public function __construct(
        Attribute $attributeSetup,
        Block $blockSetup
    ) {
        $this->_attributeSetup = $attributeSetup;
        $this->_blockSetup = $blockSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->_attributeSetup->install(['FedEx_CrossBorder::fixtures/attributes.csv']);
        $this->_blockSetup->install(['FedEx_CrossBorder::fixtures/blocks.csv']);
    }
}
