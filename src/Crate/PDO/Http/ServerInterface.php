<?php
/**
 * @copyright Interactive Solutions
 */

declare(strict_types=1);

namespace Crate\PDO\Http;

use Crate\Stdlib\CollectionInterface;
use Psr\Http\Message\ResponseInterface;

interface ServerInterface
{
    /**
     * Set the connection timeout
     *
     * @param int $timeout
     *
     * @return void
     */
    public function setTimeout(int $timeout): void;

    /**
     * Set the connection http basic auth
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    public function setHttpBasicAuth(string $username, string $password): void;

    /**
     * Set HTTP header for client requests
     *
     * @param string       $name
     * @param string       $value
     *
     * @return void
     */
    public function setHttpHeader(string $name, string $value): void;

    /**
     * @param string $query
     * @param array  $parameters
     *
     * @return CollectionInterface
     */
    public function execute(string $query, array $parameters = []): CollectionInterface;

    /**
     * @return array
     */
    public function getServerInfo(): array;

    /**
     * @return string
     */
    public function getServerVersion(): string;
}
