<?php
/**
 * @author Antoine Hedgcock
 */

namespace Crate\Stdlib;

use Countable;
use Iterator;

class Collection implements Iterator, Countable
{
    /**
     * @var array
     */
    private $rows;

    /**
     * @var string[]
     */
    private $columns;

    /**
     * @var int
     */
    private $duration;

    /**
     * @var int
     */
    private $rowCount;

    /**
     * @param array    $rows
     * @param string[] $columns
     * @param int      $duration
     * @param int      $rowCount
     */
    public function __construct(array $rows, array $columns, $duration, $rowCount)
    {
        $this->rows     = $rows;
        $this->columns  = $columns;
        $this->duration = $duration;
        $this->rowCount = $rowCount;
    }

    /**
     * @return string[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * {@Inheritdoc}
     */
    public function current()
    {
        return current($this->rows);
    }

    /**
     * {@Inheritdoc}
     */
    public function next()
    {
        next($this->rows);
    }

    /**
     * {@Inheritdoc}
     */
    public function key()
    {
        return key($this->rows);
    }

    /**
     * {@Inheritdoc}
     */
    public function valid()
    {
        return $this->key() !== null;
    }

    /**
     * {@Inheritdoc}
     */
    public function rewind()
    {
        reset($this->rows);
    }

    /**
     * {@Inheritdoc}
     */
    public function count()
    {
        return $this->rowCount;
    }
}
