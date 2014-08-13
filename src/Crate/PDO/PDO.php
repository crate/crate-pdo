<?php
// @todo license headers to be added

namespace Crate\PDO;

use Crate\PDO\ArtaxExt\ClientInterface;
use PDO as BasePDO;
use Traversable;

class PDO extends BasePDO
{
    const VERSION     = '1.0.0-dev';
    const DRIVER_NAME = 'crate';

    /**
     * The DSN in the use-case of crate should be the URI endpoint
     *
     * @var string
     */
    private $dsn;

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
        // Store the DSN for later
        $this->dsn = $dsn;

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
            foreach ($options as $attr => $value) {
                $this->setAttribute($attr, $value);
            }
        }
    }

    public function getClient()
    {
        if ($this->client === null) {
            $this->client = new ArtaxExt\Client($this->dsn, [
                'connectTimeout' => $this->attributes['timeout']
            ]);
        }

        return $this->client;
    }

    public function setClient(ClientInterface $client)
    {
        $client->setUri($this->dsn);
        $client->setTimeout($this->attributes['timeout']);

        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function prepare($statement, $options = null)
    {
        return new PDOStatement($this->getClient(), $statement, $this->attributes);
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
    public function setAttribute($attribute, $value)
    {
        switch ($attribute)
        {
            case self::ATTR_DEFAULT_FETCH_MODE:
                $this->attributes['defaultFetchMode'] = $value;
                break;

            case self::ATTR_ERRMODE:
                $this->attributes['errorMode'] = $value;
                break;

            case self::ATTR_TIMEOUT:
                $this->attributes['timeout'] = (int) $value;
                $this->getClient()->setTimeout((int) $value);
                break;

            default:
                throw new Exception\PDOException('Unsupported driver attribute');
        }
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
                return static::DRIVER_NAME;

            case PDO::ATTR_STATEMENT_CLASS:
                return [$this->attributes['statementClass']];

            default:
                // PHP Switch a lose comparison
                if ($attribute === PDO::ATTR_AUTOCOMMIT) {
                    return true;
                }

                throw new Exception\PDOException('Unsupported driver attribute');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function quote($string, $parameter_type = PDO::PARAM_STR)
    {
        switch ($parameter_type)
        {
            case PDO::PARAM_INT:
                return (int) $string;

            case PDO::PARAM_BOOL:
                return (bool) $string;

            case PDO::PARAM_NULL:
                return null;

            default:
                throw new Exception\UnsupportedException;
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getAvailableDrivers()
    {
        return array_merge(parent::getAvailableDrivers(), [static::DRIVER_NAME]);
    }
}
