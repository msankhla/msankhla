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
namespace FedEx\CrossBorder\Console\Command\Import;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FedEx\CrossBorder\Model\AvailableCountries\ImportProcess as Process;

class AvailableCountriesCommand extends Command
{
    /**
     * @var Process
     */
    protected $_process;

    /**
     * @var OutputInterface
     */
    protected $_output;

    /**
     * @var State
     */
    protected $_state;

    /**
     * ImportWarehouseCommand constructor.
     *
     * @param Process $process
     * @param State $state
     * @param null $name
     */
    public function __construct(
        Process $process,
        State $state,
        $name = null
    ) {
        $this->_process = $process;
        $this->_state = $state;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(
            'fdxcb:import:available-countries'
        )->setDescription(
            'Import available countries from FedEx Cross Border.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->_output = $output;
        $this->_process->start($output);
    }
}