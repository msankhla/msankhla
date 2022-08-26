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
namespace FedEx\CrossBorder\Model\AvailableCountries;

use FedEx\Core\Model\AbstractProcess;
use FedEx\Core\Model\Progress;

class ImportProcess extends AbstractProcess
{
    /**
     * @var Import
     */
    protected $_import;

    /**
     * ImportProcess constructor.
     *
     * @param Import $import
     * @param Progress $progress
     */
    public function __construct(
        Import $import,
        Progress $progress
    ) {
        $this->_import = $import;
        parent::__construct($progress);
    }

    public function prepareData()
    {
        $this->getProgress()->setTitle('Loading data')->reset()->setCount(1)->show(true);
        $data = $this->_import->getResponse();
        $this->getProgress()->add()->show();
        $this->getProgress()->newLine();

        return $data;
    }

    /**
     * Execute method
     *
     * @return $this
     */
    public function execute()
    {
        $data = $this->prepareData();

        if (!empty($data)) {
            $this->getProgress()->setTitle('Importing data')->reset()->setCount(
                count($data)
            )->show(true);

            foreach ($data as $rec) {
                $this->_import->addItem($rec);
                $this->getProgress()->add()->show();
            }
        }

        $this->getProgress()->newLine();
    }
}