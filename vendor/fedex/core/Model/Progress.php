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

class Progress
{
    /**
     * @var int
     */
    protected $_count;

    /**
     * @var int
     */
    protected $_currentValue;

    /**
     * @var int
     */
    protected $_length  = 0;

    /**
     * @var OutputInterface
     */
    protected $_output;

    /**
     * @var string
     */
    protected $_pattern = '%-\' 30s %\' 3d%% %\' 30s %\' 40s';

    /**
     * @var int
     */
    protected $_progress;

    /**
     * @var int
     */
    protected $_timeStart;

    /**
     * @var string
     */
    protected $_title   = 'Progress';

    /**
     * Sets output
     *
     * @param OutputInterface|null $output
     * @return $this
     */
    public function setOutput(OutputInterface $output = null)
    {
        $this->_output = $output;

        return $this;
    }

    /**
     * Gets output
     *
     * @param OutputInterface|null $output
     * @return $this
     */
    public function getOutput(OutputInterface $output = null)
    {
        $this->_output = $output;

        return $this;
    }


    /**
     * Sets count
     *
     * @param int $value
     * @return $this
     */
    public function setCount($value)
    {
        $this->_count = $value;

        return $this;
    }

    /**
     * Returns count
     *
     * @return int
     */
    public function getCount()
    {
        return $this->_count;
    }

    /**
     * Adds value
     *
     * @param int $value
     * @return $this
     */
    public function add($value = 1)
    {
        $this->_currentValue += $value;

        return $this;
    }

    /**
     * Sets current value
     *
     * @param int $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->_currentValue = $value;

        return $this;
    }

    /**
     * Returns current value
     *
     * @return int
     */
    public function getValue()
    {
        return $this->_currentValue;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setTitle($value)
    {
        $this->_title = $value;

        return $this;
    }

    /**
     * Returns title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Returns message pattern
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->_pattern;
    }

    /**
     * Reset data
     *
     * @return $this
     */
    public function reset()
    {
        $this->_currentValue = 0;
        $this->_progress = 0;
        $this->_length = 0;
        $this->_timeStart = null;
        return $this;
    }

    /**
     * Returns progress value
     *
     * @return float
     */
    public function getProgress()
    {
        return floor($this->getCount() > 0 ? 100 * $this->getValue() / $this->getCount() : 0);
    }

    /**
     * Converting time
     *
     * @param int $time
     * @return string
     */
    public function convertTime($time)
    {
        $result = '';
        $list = [
            'day(s)'    => 24 * 60 * 60,
            'hour(s)'   => 60 * 60,
            'min'       => 60,
        ];
        foreach ($list as $prefix => $val) {
            $v = floor($time / $val);
            $time -= $v * $val;
            if ($v) {
                $result .= (!empty($result) ? ' ' : '') . sprintf('%s %s', $v, $prefix);
            }
        }
        $result .= (!empty($result) ? ' ' : '') . sprintf('%s %s', number_format($time, 3, '.', ''), 'sec');

        return $result;
    }

    /**
     * Returns micro time
     *
     * @return float
     */
    public function getTime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Show message
     *
     * @return $this
     */
    public function show($isFirst = false)
    {
        if (!isset($this->_output)) {
            return $this;
        }

        if (!isset($this->_timeStart)) {
            $this->_timeStart = $this->getTime();
        }
        $progress = $this->getProgress();
        if ($progress != $this->_progress || $isFirst) {
            $this->_progress = $progress;
            $st = sprintf(
                $this->getPattern(),
                $this->getTitle(),
                $progress,
                number_format($this->getValue(), 0, '.' , ' ') . ' / ' . number_format($this->getCount(), 0, '.' , ' '),
                $this->convertTime($this->getTime() - $this->_timeStart)
            );
            $this->_output->write("\r" . str_pad($st, $this->_length, ' '));
            $this->_length = strlen($st);
        }

        return $this;
    }

    /**
     * Write new line
     *
     * @return $this
     */
    public function newLine()
    {
        if (!isset($this->_output)) {
            return $this;
        }

        $this->_output->writeln('');

        return $this;
    }
}