<?php
// @todo license headers to be added

namespace Crate\PDO;

use PDO as BasePDO;
use Traversable;

class PDO extends BasePDO
{
    const VERSION = '1.0.0-dev';

    /**
     * @var array
     */
    private $attributes = [
        'defaultFetchMode' => self::FETCH_BOTH,
        'errorMode'        => self::ERRMODE_SILENT,
        'statementClass'   => 'Crate\PDO\PDOStatement',

        'resultObject'                => 'PDOObject',
        'resultObjectConstructorArgs' => [],

        'timeout' => 5
    ];

    /**
     * @var ArtaxExt\Client
     */
    private $client;

    /**
     * {@inheritDoc}
     */
    public function __construct($dsn, $username, $passwd, $options)
    {
        $this->client = new ArtaxExt\Client($dsn, [
            'connectTimeout' => $this->attributes['timeout']
        ]);

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
            $this->attributes = array_merge($this->attributes, $options);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function prepare($statement, $options = null)
    {
        return new PDOStatement($this->client, $statement, $this->attributes);
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
        $this->attributes[$attribute] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function exec($statement)
    {
        $statement = $this->prepare($statement);
        $result    = $statement->execute();

        return $result !== false ? $statement->rowCount() : false;
    }

    /**
     * {@inheritDoc}
     */
    public function query($statement)
    {
        $statement = $this->prepare($statement);
        $result    = $statement->execute();

        return $result !== false ? $statement : false;
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
        switch ($attribute)
        {
            case PDO::ATTR_PERSISTENT:
                return false;

            case PDO::ATTR_PREFETCH:
                return false;

            case PDO::ATTR_AUTOCOMMIT:
                return true;

            case PDO::ATTR_CLIENT_VERSION:
                return self::VERSION;

            case PDO::ATTR_SERVER_VERSION:
                return $this->client->getServerVersion();

            case PDO::ATTR_SERVER_INFO:
                return $this->client->getServerInfo();

            case PDO::ATTR_TIMEOUT:
                return $this->attributes['timeout'];

            case PDO::ATTR_DEFAULT_FETCH_MODE:
                return $this->attributes['defaultFetchMode'];

            case PDO::ATTR_ERRMODE:
                return $this->attributes['errorMode'];

            case PDO::ATTR_DRIVER_NAME:
                return 'crate';

            case PDO::ATTR_STATEMENT_CLASS:
                return [$this->attributes['statementClass']];
        }
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
        return array_merge(parent::getAvailableDrivers(), ['crate']);
    }
}
