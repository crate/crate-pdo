<?php
/**
 * @author Antoine Hedgcock
 */

namespace Crate\Stdlib;

use Countable;
use Iterator;

class Collection implements CollectionInterface
{
    /**
     * @var array
     */
    private $rows;

    /**
     * @var string[]
     */
    private $columnsAsKeys;

    /**
     * @var string[]
     */
    private $columnsAsValues;

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
        $this->rows            = $rows;
        $this->columnsAsKeys   = array_flip($columns);
        $this->columnsAsValues = $columns;
        $this->duration        = $duration;
        $this->rowCount        = $rowCount;
    }

    /**
     * {@Inheritdoc}
     */
    public function map(callable $callback)
    {
        return array_map($callback, $this->rows);
    }

    /**
     * {@Inheritdoc}
     */
    public function getColumnIndex($column)
    {
        if (isset($this->columnsAsKeys[$column])) {
            return $this->columnsAsKeys[$column];
        }

        return null;
    }

    /**
     * {@Inheritdoc}
     */
    public function getColumns($columnsAsKeys = true)
    {
        return $columnsAsKeys ? $this->columnsAsKeys : $this->columnsAsValues;
    }

    /**
     * {@Inheritdoc}
     */
    public function getRows()
    {
        return $this->rows;
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
