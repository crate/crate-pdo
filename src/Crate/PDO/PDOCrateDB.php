<?php
/**
 * Licensed to CRATE Technology GmbH("Crate") under one or more contributor
 * license agreements.  See the NOTICE file distributed with this work for
 * additional information regarding copyright ownership.  Crate licenses
 * this file to you under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.  You may
 * obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.  See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * However, if you have executed another commercial license agreement
 * with Crate these terms will supersede the license and you may use the
 * software solely pursuant to the terms of the relevant commercial agreement.
 */

declare(strict_types=1);

namespace Crate\PDO;

use Crate\PDO\Exception\InvalidArgumentException;
use Crate\PDO\Exception\PDOException;
use Crate\PDO\Http\ServerInterface;
use Crate\PDO\Http\ServerPool;
use Crate\Stdlib\ArrayUtils;
use PDO as BasePDO;

use const PHP_VERSION_ID;

class PDOCrateDB extends BasePDO implements PDOInterface
{
    use PDOImplementation;

    public const VERSION     = '2.2.3';
    public const DRIVER_NAME = 'crate';

    public const DSN_REGEX = '/^(?:crate:)(?:((?:[\w\.-]+:\d+\,?)+))\/?([\w]+)?$/';

    public const CRATE_ATTR_HTTP_BASIC_AUTH = 1000;
    public const CRATE_ATTR_DEFAULT_SCHEMA  = 1001;

    public const CRATE_ATTR_SSL_MODE                                       = 1008;
    public const CRATE_ATTR_SSL_MODE_DISABLED                              = 1;
    public const CRATE_ATTR_SSL_MODE_ENABLED_BUT_WITHOUT_HOST_VERIFICATION = 2;
    public const CRATE_ATTR_SSL_MODE_REQUIRED                              = 3;

    public const CRATE_ATTR_SSL_KEY_PATH      = 1002;
    public const CRATE_ATTR_SSL_KEY_PASSWORD  = 1003;
    public const CRATE_ATTR_SSL_CERT_PATH     = 1004;
    public const CRATE_ATTR_SSL_CERT_PASSWORD = 1005;
    public const CRATE_ATTR_SSL_CA_PATH       = 1006;
    public const CRATE_ATTR_SSL_CA_PASSWORD   = 1007;

    public const PARAM_FLOAT     = 6;
    public const PARAM_DOUBLE    = 7;
    public const PARAM_LONG      = 8;
    public const PARAM_ARRAY     = 9;
    public const PARAM_OBJECT    = 10;
    public const PARAM_TIMESTAMP = 11;
    public const PARAM_IP        = 12;

    /**
     * @var array
     */
    private $attributes = [
        'defaultFetchMode' => self::FETCH_BOTH,
        'errorMode'        => self::ERRMODE_SILENT,
        'sslMode'          => self::CRATE_ATTR_SSL_MODE_DISABLED,
        'statementClass'   => [PDOStatement::class, []],
        'timeout'          => 0.0,
        'auth'             => [],
        'defaultSchema'    => 'doc',
        'bulkMode'         => false,
    ];

    /**
     * @var Http\ServerInterface
     */
    private $server;

    /**
     * @var PDOStatement|null
     */
    private $lastStatement;

    /**
     * @var callable
     */
    private $request;

    /**
     * {@inheritDoc}
     *
     * @param string     $dsn      The HTTP endpoint to call
     * @param null       $username Username for basic auth
     * @param null       $passwd   Password for basic auth
     * @param null|array $options  Attributes to set on the PDO
     */
    public function __construct($dsn, $username = null, $passwd = null, $options = [])
    {

        if (PHP_VERSION_ID < 80000) {
            trigger_error(
                "`crate/crate-pdo` will stop supporting PHP7 on one of the upcoming " .
                "releases. Please upgrade to PHP8.",
                E_USER_DEPRECATED
            );
        }

        $dsnParts = self::parseDSN($dsn);
        $servers  = self::serversFromDsnParts($dsnParts);

        $this->setServer(new ServerPool($servers));

        foreach ((array)$options as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }

        if (!empty($username)) {
            $this->setAttribute(self::CRATE_ATTR_HTTP_BASIC_AUTH, [$username, $passwd]);
        }

        if (!empty($dsnParts[1])) {
            $this->setAttribute(self::CRATE_ATTR_DEFAULT_SCHEMA, $dsnParts[1]);
        }

        // Define a callback that will be used in the PDOStatements
        // This way we don't expose this as a public api to the end users.
        $this->request = function (PDOStatement $statement, $sql, array $parameters) {

            $this->lastStatement = $statement;

            try {
                if ($statement->isBulkMode()) {
                    return $this->server->executeBulk($sql, $parameters);
                } else {
                    return $this->server->execute($sql, $parameters);
                }
            } catch (Exception\RuntimeException $e) {
                if ($this->getAttribute(self::ATTR_ERRMODE) === self::ERRMODE_EXCEPTION) {
                    throw new Exception\PDOException($e->getMessage(), $e->getCode());
                }

                if ($this->getAttribute(self::ATTR_ERRMODE) === self::ERRMODE_WARNING) {
                    trigger_error(sprintf('[%d] %s', $e->getCode(), $e->getMessage()), E_USER_WARNING);
                }

                // should probably wrap this in a error object ?
                return [
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                ];
            }
        };
    }

    /**
     * Change the server implementation
     *
     * @param ServerInterface $server
     */
    public function setServer(ServerInterface $server): void
    {
        $this->server = $server;
        $this->server->configure($this);
    }

    /**
     * Extract servers and optional custom schema from DSN string
     *
     * @param string $dsn The DSN string
     *
     * @throws \Crate\PDO\Exception\PDOException on an invalid DSN string
     *
     * @return array An array of ['host:post,host:port,...', 'schema']
     */
    private static function parseDSN($dsn)
    {
        $matches = [];

        if (!preg_match(static::DSN_REGEX, $dsn, $matches)) {
            throw new PDOException(sprintf('Invalid DSN %s', $dsn));
        }

        return array_slice($matches, 1);
    }

    /**
     * Extract host:port pairs out of the DSN parts
     *
     * @param array $dsnParts The parts of the parsed DSN string
     *
     * @return array An array of host:port strings
     */
    private static function serversFromDsnParts($dsnParts)
    {
        return explode(',', trim($dsnParts[0], ','));
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function prepare($statement, $options = null)
    {
        $options = ArrayUtils::toArray($options);

        if (isset($options[self::ATTR_CURSOR])) {
            trigger_error(sprintf('%s not supported', __METHOD__), E_USER_WARNING);

            return true;
        }

        $statementClass = $this->attributes['statementClass'];
        if (is_string($statementClass)) {
            trigger_error(
                "Using bare class strings with `statementClass` is deprecated, " .
                "see also https://github.com/crate/crate-pdo/issues/191.",
                E_USER_DEPRECATED
            );
            $className = $statementClass;
            $constructorArgs = [];
        } elseif (is_array($statementClass)) {
            $className = $statementClass[0];
            $constructorArgs = $statementClass[1] ?? [];
        } else {
            throw new InvalidArgumentException(
                'Value provided to statementClass has invalid type'
            );
        }
        if ($className === PDOStatement::class) {
            $constructorArgs = [$this, $this->request, $statement, $options];
        }

        return new $className(...$constructorArgs);
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function commit(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function rollBack(): bool
    {
        throw new Exception\UnsupportedException;
    }

    /**
     * {@inheritDoc}
     */
    public function inTransaction(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function exec($statement)
    {
        $statement = $this->prepare($statement);
        $result    = $statement->execute();

        return $result === false ? false : $statement->rowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function doQuery($statement, ?int $fetchMode = null, ...$fetchModeArgs)
    {
        $statement = $this->prepare($statement);
        if ($fetchMode !== null) {
            $statement->setFetchMode($fetchMode, ...$fetchModeArgs);
        }

        $result    = $statement->execute();

        return $result === false ? false : $statement;
    }

    /**
     * {@inheritDoc}
     */
    public function lastInsertId($name = null): string
    {
        throw new Exception\UnsupportedException;
    }

    /**
     * {@inheritDoc}
     */
    public function errorCode(): ?string
    {
        return $this->lastStatement === null ? null : $this->lastStatement->errorCode();
    }

    /**
     * {@inheritDoc}
     */
    public function errorInfo(): array
    {
        return $this->lastStatement === null ? ["00000", null, null] : $this->lastStatement->errorInfo();
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Crate\PDO\Exception\PDOException
     * @throws \Crate\PDO\Exception\InvalidArgumentException
     */
    #[\ReturnTypeWillChange]
    public function setAttribute($attribute, $value)
    {
        switch ($attribute) {
            case self::ATTR_DEFAULT_FETCH_MODE:
                $this->attributes['defaultFetchMode'] = $value;
                break;

            case self::ATTR_ERRMODE:
                $this->attributes['errorMode'] = $value;
                break;

            case self::ATTR_STATEMENT_CLASS:
                // Previous versions accepted bare class strings on this mode's argument value,
                // while the PDO standard format is `[ClassName::class, [constructor_args]]`.
                // Let's modernize, upcycle, and propagate accordingly.
                if (is_string($value)) {
                    trigger_error(
                        "Using bare class strings with `ATTR_STATEMENT_CLASS` is deprecated, " .
                        "see also https://github.com/crate/crate-pdo/issues/191.",
                        E_USER_DEPRECATED
                    );
                    $this->attributes['statementClass'] = [$value, []];
                } elseif (is_array($value)) {
                    if (empty($value) || !is_string($value[0])) {
                        throw new InvalidArgumentException(
                            'ATTR_STATEMENT_CLASS array must contain a class name as first element'
                        );
                    }
                    $this->attributes['statementClass'] = $value;
                } else {
                    throw new InvalidArgumentException(
                        'Value provided to ATTR_STATEMENT_CLASS has invalid type'
                    );
                }
                break;

            case self::ATTR_TIMEOUT:
                $this->attributes['timeout'] = (int)$value;
                break;

            case self::CRATE_ATTR_HTTP_BASIC_AUTH:
                if (!is_array($value) && $value !== null) {
                    throw new InvalidArgumentException(
                        'Value provided to CRATE_ATTR_HTTP_BASIC_AUTH must be null or an array'
                    );
                }

                $this->attributes['auth'] = $value;
                break;

            case self::CRATE_ATTR_DEFAULT_SCHEMA:
                $this->attributes['defaultSchema'] = $value;
                break;

            case self::CRATE_ATTR_SSL_MODE:
                $this->attributes['sslMode'] = $value;
                break;

            case self::CRATE_ATTR_SSL_CA_PATH:
                $this->attributes['sslCa'] = $value;
                break;

            case self::CRATE_ATTR_SSL_CA_PASSWORD:
                $this->attributes['sslCaPassword'] = $value;
                break;

            case self::CRATE_ATTR_SSL_CERT_PATH:
                $this->attributes['sslCert'] = $value;
                break;

            case self::CRATE_ATTR_SSL_CERT_PASSWORD:
                $this->attributes['sslCertPassword'] = $value;
                break;

            case self::CRATE_ATTR_SSL_KEY_PATH:
                $this->attributes['sslKey'] = $value;
                break;

            case self::CRATE_ATTR_SSL_KEY_PASSWORD:
                $this->attributes['sslKeyPassword'] = $value;
                break;

            default:
                throw new Exception\PDOException('Unsupported driver attribute');
        }

        // A setting changed so we need to reconfigure the server pool
        $this->server->configure($this);
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Crate\PDO\Exception\PDOException
     */
    #[\ReturnTypeWillChange]
    public function getAttribute($attribute)
    {
        switch ($attribute) {
            case self::ATTR_PREFETCH:
            case self::ATTR_PERSISTENT:
                return false;

            case self::ATTR_CLIENT_VERSION:
                return self::VERSION;

            case self::ATTR_SERVER_VERSION:
                return $this->server->getServerVersion();

            case self::ATTR_SERVER_INFO:
                return $this->server->getServerInfo();

            case self::ATTR_TIMEOUT:
                return $this->attributes['timeout'];

            case self::CRATE_ATTR_HTTP_BASIC_AUTH:
                return $this->attributes['auth'];

            case self::ATTR_DEFAULT_FETCH_MODE:
                return $this->attributes['defaultFetchMode'];

            case self::ATTR_ERRMODE:
                return $this->attributes['errorMode'];

            case self::ATTR_DRIVER_NAME:
                return static::DRIVER_NAME;

            case self::ATTR_STATEMENT_CLASS:
                return $this->attributes['statementClass'];

            case self::CRATE_ATTR_DEFAULT_SCHEMA:
                return $this->attributes['defaultSchema'];

            case self::CRATE_ATTR_SSL_MODE:
                return $this->attributes['sslMode'];

            case self::CRATE_ATTR_SSL_CA_PATH:
                return $this->attributes['sslCa'] ?? null;

            case self::CRATE_ATTR_SSL_CA_PASSWORD:
                return $this->attributes['sslCaPassword'] ?? null;

            case self::CRATE_ATTR_SSL_CERT_PATH:
                return $this->attributes['sslCert'] ?? null;

            case self::CRATE_ATTR_SSL_CERT_PASSWORD:
                return $this->attributes['sslCertPassword'] ?? null;

            case self::CRATE_ATTR_SSL_KEY_PATH:
                return $this->attributes['sslKey'] ?? null;

            case self::CRATE_ATTR_SSL_KEY_PASSWORD:
                return $this->attributes['sslKeyPassword'] ?? null;

            default:
                // PHP Switch is a lose comparison
                if ($attribute === self::ATTR_AUTOCOMMIT) {
                    return true;
                }

                throw new Exception\PDOException(sprintf('Unsupported driver attribute: %s', $attribute));
        }
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function quote($string, $parameter_type = self::PARAM_STR)
    {
        switch ($parameter_type) {
            case self::PARAM_INT:
                return (int)$string;

            case self::PARAM_BOOL:
                return (bool)$string;

            case self::PARAM_NULL:
                return null;

            case self::PARAM_LOB:
                throw new Exception\UnsupportedException('This is not supported by crate.io');

            case self::PARAM_STR:
                trigger_error(
                    "Strongly consider using prepared statements (secure) " .
                    "instead of quoting strings manually (insecure), " .
                    "see also https://www.php.net/manual/en/pdo.quote.php.",
                    E_USER_DEPRECATED
                );
                return $this->quotePostgresql((string)$string);

            default:
                throw new Exception\InvalidArgumentException('Unknown param type');
        }
    }

    /**
     * Escape/quote strings for PostgreSQL when using prepared statements is not possible.
     *
     * https://github.com/ADOdb/ADOdb/blob/v5.22.10/adodb.inc.php
     * https://github.com/ADOdb/ADOdb/blob/v5.22.10/drivers/adodb-postgres64.inc.php
     *
     * @param string $string
     * @return string
     */
    private function quotePostgresql($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        $value = str_replace(
            array('\\', "\0"),
            array('\\\\', "\\\0"),
            $value
        );
        // CrateDB uses quote-doubling instead of backslash escaping.
        return str_replace("'", "''", $value);
    }

    /**
     * {@inheritDoc}
     */
    public static function getAvailableDrivers(): array
    {
        return array_merge(parent::getAvailableDrivers(), [static::DRIVER_NAME]);
    }

    public function getServerVersion(): string
    {
        return $this->server->getServerVersion();
    }

    public function getServerInfo(): string
    {
        return $this->getServerVersion();
    }
}
