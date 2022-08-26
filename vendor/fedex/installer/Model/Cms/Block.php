<?php
/**
 * FedEx Installer component
 *
 * @category    FedEx
 * @package     FedEx_Installer
 * @author      Andrey Rapshtynsky <andrey.bh@gmail.com>
 * @copyright   FedEx (https://crossborder.fedex.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace FedEx\Installer\Model\Cms;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Store\Model\Store;

class Block
{
    /**
     * @var BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var Csv
     */
    protected $_csvReader;

    /**
     * Block constructor.
     *
     * @param SampleDataContext $sampleDataContext
     */
    public function __construct(
        BlockFactory $blockFactory,
        SampleDataContext $sampleDataContext
    ) {
        $this->_blockFactory = $blockFactory;
        $this->_csvReader = $sampleDataContext->getCsvReader();
        $this->_fixtureManager = $sampleDataContext->getFixtureManager();
    }

    /**
     * Installing process
     *
     * @param array $fixtures
     */
    public function install(array $fixtures)
    {
        foreach ($fixtures as $fileName) {
            $fileName = $this->_fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->_csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                };
                $cmsBlock = $this->saveCmsBlock($data);
                $cmsBlock->unsetData();
            }
        }
    }

    /**
     * Save cms block
     *
     * @param array $data
     * @return \Magento\Cms\Model\Block
     */
    protected function saveCmsBlock($data)
    {
        /** @var \Magento\Cms\Model\Block $cmsBlock */
        $cmsBlock = $this->_blockFactory->create();
        $cmsBlock->getResource()->load($cmsBlock, $data['identifier']);
        if (!$cmsBlock->getData()) {
            $cmsBlock->setData($data);
        } else {
            $cmsBlock->addData($data);
        }
        $cmsBlock->setStores([Store::DEFAULT_STORE_ID]);
        $cmsBlock->setIsActive(1);
        $cmsBlock->save();

        return $cmsBlock;
    }
}
