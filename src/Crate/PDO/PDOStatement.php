<?php
// @todo license headers to be added

namespace Crate\PDO;

use PDOStatement as BasePDOStatement;

class PDOStatement extends BasePDOStatement
{
    /**
     * {@inheritDoc}
     */
    public $queryString;

    /**
     * {@inheritDoc}
     */
    public function execute($input_parameters = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function bindParam(
        $parameter,
        & $variable,
        $data_type = PDO::PARAM_STR,
        $length = null,
        $driver_options = null
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function rowCount()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function fetchColumn($column_number = 0)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = array())
    {
    }

    /**
     * {@inheritDoc}
     */
    public function fetchObject($class_name = "stdClass", $ctor_args = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function errorCode()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function errorInfo()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function setAttribute($attribute, $value)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute($attribute)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function columnCount()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnMeta($column)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function setFetchMode($mode, $params = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function nextRowset()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function closeCursor()
    {
        throw new Exception\UnsupportedException;
    }

    /**
     * {@inheritDoc}
     */
    public function debugDumpParams()
    {
    }
}
