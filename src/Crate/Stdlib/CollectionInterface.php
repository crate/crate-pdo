<?php
/**
 * @author Antoine Hedgcock
 */

namespace Crate\Stdlib;

use Countable;
use Iterator;

interface CollectionInterface extends Countable, Iterator
{
    /**
     * Get the columns as either an array where the keys point to the index or vice versa
     *
     * @param bool $columnsAsKeys
     *
     * @return string[]
     */
    public function getColumns($columnsAsKeys = true);

    /**
     * Get the column index
     *
     * @param string $column
     *
     * @return string|null
     */
    public function getColumnIndex($column);

    /**
     * Apply a callback to each item in the collection
     *
     * @param callable $callable
     *
     * @return array
     */
    public function map(callable $callable);

    /**
     * Get all the rows
     *
     * @return array
     */
    public function getRows();
}
