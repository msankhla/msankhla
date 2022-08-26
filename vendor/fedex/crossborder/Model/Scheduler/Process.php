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
namespace FedEx\CrossBorder\Model\Scheduler;

use FedEx\Core\Model\AbstractProcess;
use FedEx\Core\Model\Progress;
use FedEx\CrossBorder\Model\SchedulerManagement;

class Process extends AbstractProcess
{
    /**
     * @var SchedulerManagement
     */
    protected $_schedulerManagement;

    /**
     * RunProcess constructor.
     * @param SchedulerManagement $schedulerManagement
     * @param Progress $progress
     */
    public function __construct(
        SchedulerManagement $schedulerManagement,
        Progress $progress
    ) {
        $this->_schedulerManagement = $schedulerManagement;
        parent::__construct($progress);
    }

    /**
     * Execute method
     *
     * @return $this
     */
    public function execute()
    {
        $collection = $this->_schedulerManagement->getAvailableSchedulers();

        if ($count = count($collection)) {
            $this->getProgress()->setTitle('Scheduler')->reset()->setCount(
                $count
            )->show(true);

            foreach ($collection as $scheduler) {
                $scheduler->run(true);
                $this->getProgress()->add()->show();
            }
        }

        $this->getProgress()->newLine();

        return $this;
    }
}