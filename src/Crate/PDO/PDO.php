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
    public function prepare($statement, array $driver_options = array())
    {

    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction()
    {
        // @todo CRATE.IO does not support transactions
    }

    /**
     * {@inheritDoc}
     */
    public function commit()
    {
        // @todo CRATE.IO does not support transactions
    }

    /**
     * {@inheritDoc}
     */
    public function rollBack()
    {
        // @todo CRATE.IO does not support transactions - throw an exception?
    }

    /**
     * {@inheritDoc}
     */
    public function inTransaction()
    {
        // @todo CRATE.IO does not support transactions
        return false;
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
        // should probably store it locally
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
