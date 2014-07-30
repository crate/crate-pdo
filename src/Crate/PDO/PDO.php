<?php
// @todo license headers to be added

namespace Crate\PDO;

use PDO as BasePDO;

class PDO extends BasePDO
{
    /**
     * {@inheritDoc}
     */
    public function __construct($dsn, $username, $passwd, $options)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function prepare($statement, $options = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction()
    {
        throw new Exception\UnsupportedException;
    }

    /**
     * {@inheritDoc}
     */
    public function commit()
    {
        throw new Exception\UnsupportedException;
    }

    /**
     * {@inheritDoc}
     */
    public function rollBack()
    {
        throw new Exception\UnsupportedException;
    }

    /**
     * {@inheritDoc}
     */
    public function inTransaction()
    {
        throw new Exception\UnsupportedException;
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
    public function exec($statement)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function query($statement)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function lastInsertId($name = null)
    {
        throw new Exception\UnsupportedException;
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
    public function getAttribute($attribute)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function quote($string, $parameter_type = PDO::PARAM_STR)
    {

    }

    /**
     * {@inheritDoc}
     */
    public static function getAvailableDrivers()
    {

    }
}
