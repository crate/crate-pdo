<?php
// @todo license headers to be added

namespace Crate\PDO;

use PDO as BasePDO;
use Traversable;

class PDO extends BasePDO
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var ArtaxExt\Client
     */
    private $client;

    /**
     * {@inheritDoc}
     */
    public function __construct($dsn, $username, $passwd, $options)
    {
        $this->client = new ArtaxExt\Client($dsn);

        if ($options instanceof Traversable) {
            $options = iterator_to_array($options);
        }

        if ($options !== null && !is_array($options)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Fourth argument of __construct is expected to be traversable or null "%s" received.',
                    gettype($options)
                )
            );
        } else {
            $this->options = $options;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function prepare($statement, $options = null)
    {
        return new PDOStatement($this->client, $statement);
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
        $statement = $this->prepare($statement);
        $statement->execute();

        return $statement->rowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function query($statement)
    {
        $statement = $this->prepare($statement);
        $statement->execute();

        return $statement;
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
        $statement = $this->client->getLastStatement();
        return $statement !== null ? $statement->errorCode() : null;
    }

    /**
     * {@inheritDoc}
     */
    public function errorInfo()
    {
        $statement = $this->client->getLastStatement();
        return $statement !== null ? $statement->errorInfo() : null;
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
        return parent::getAvailableDrivers() + ['crate'];
    }
}
