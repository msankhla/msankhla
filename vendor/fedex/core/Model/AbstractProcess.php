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
namespace FedEx\Core\Model;

use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractProcess
{
    const TITLE                 = 'Process Started';
    const STATUS_COMPLETED      = 'Completed';
    const STATUS_IN_PROGRESS    = 'The process already in progress';

    const PROCESS_CODE          = '';


    /**
     * @var bool
     */
    protected $_isError = false;

    /**
     * @var string
     */
    protected $_message;

    /**
     * @var Progress
     */
    protected $_progress;

    /**
     * @var OutputInterface
     */
    protected $_output;

    /**
     * AbstractProcess constructor.
     *
     * @param Progress $progress
     */
    public function __construct(
        Progress $progress
    ) {
        $this->_progress = $progress;
    }

    /**
     * Checks if was error
     *
     * @return bool
     */
    public function isError()
    {
        return (bool) $this->_isError;
    }

    /**
     * Returns current message
     *
     * @return string
     */
    public function getMessage()
    {
        return (isset($this->_message) ? $this->_message : '');
    }

    /**
     * Sets current message
     *
     * @param string $value
     * @return $this
     */
    public function setMessage($value = null)
    {
        $this->_message = $value;

        return $this;
    }

    /**
     * Returns progress model
     *
     * @return Progress
     */
    public function getProgress()
    {
        return $this->_progress;
    }

    /**
     * Returns output
     *
     * @return OutputInterface|null
     */
    public function getOutput()
    {
        return $this->_output;
    }

    /**
     * Sets output
     *
     * @param OutputInterface|null $output
     * @return $this
     */
    public function setOutput(OutputInterface $output = null)
    {
        $this->_progress->setOutput($output);
        $this->_output = $output;

        return $this;
    }

    /**
     * Shows header
     *
     * @return $this
     */
    public function showHeader()
    {
        if ($this->getOutput()) {
            $this->getOutput()->writeln('');
            $this->getOutput()->writeln('<info>' . static::TITLE . '</info>');
        }

        return $this;
    }

    /**
     * @param OutputInterface|null $output
     * @return $this
     */
    public function start(OutputInterface $output = null)
    {
        $this->setOutput($output);

        $this->_isError = false;
        $this->showHeader(
        )->execute();

        return $this;
    }

    /**
     * Execute method
     *
     * @return $this
     */
    abstract public function execute();
}