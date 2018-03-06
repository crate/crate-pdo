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

namespace Crate\PDO\Http;

use Crate\PDO\Exception\RuntimeException;
use Crate\Stdlib\Collection;
use Crate\Stdlib\CollectionInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;

final class ServerPool implements ServerPoolInterface
{
    private const DEFAULT_SERVER = 'localhost:4200';

    /**
     * @var string[]
     */
    private $availableServers = [];

    /**
     * @var Server[]
     */
    private $serverPool = [];

    /**
     * Client constructor.
     *
     * @param array $servers
     * @param array $options
     */
    public function __construct(array $servers, array $options)
    {
        if (count($servers) === 0) {
            $servers = [self::DEFAULT_SERVER];
        }

        foreach ($servers as $server) {
            $this->serverPool[$server] = new Server($server, $options);
            $this->availableServers[]  = $server;
        }
    }

    /**
     * {@Inheritdoc}
     */
    public function execute($query, array $parameters): CollectionInterface
    {

        while (true) {
            $nextServer = $this->nextServer();
            $s          = $this->serverPool[$nextServer];

            try {
                return $s->execute($query, $parameters);
            } catch (ConnectException $exception) {
                // drop the server from the list of available servers
                $this->dropServer($nextServer);
                // break the loop if no more servers are available
                $this->raiseIfNoMoreServers($exception);
            } catch (BadResponseException $exception) {

                $json = json_decode((string)$exception->getResponse()->getBody(), true);

                if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException('Server returned non-JSON response.', 0, $exception);
                }

                $errorCode    = $json['error']['code'];
                $errorMessage = $json['error']['message'];

                throw new RuntimeException($errorMessage, $errorCode, $exception);
            }
        }
    }

    /**
     * {@Inheritdoc}
     */
    public function getServerInfo(): array
    {
        return [
            'serverVersion' => $this->getServerVersion(),
        ];
    }

    /**
     * {@Inheritdoc}
     */
    public function getServerVersion(): string
    {
        $result = $this->execute("select version['number'] from sys.nodes limit 1", []);

        if (count($result->getRows()) !== 1) {
            throw new RuntimeException('Failed to determine server version');
        }

        return $result->getRows()[0][0];
    }

    /**
     * {@Inheritdoc}
     */
    public function setTimeout(int $timeout): void
    {
        foreach ($this->serverPool as $k => $s) {
            $s->setTimeout($timeout);
        }
    }

    /**
     * {@Inheritdoc}
     */
    public function setHttpBasicAuth(string $username, string $password): void
    {
        foreach ($this->serverPool as $k => $s) {
            $s->setHttpBasicAuth($username, $password);
        }
    }

    /**
     * {@Inheritdoc}
     */
    public function setHttpHeader(string $name, string $value): void
    {
        foreach ($this->serverPool as $k => $s) {
            $s->setHttpHeader($name, $value);
        }
    }

    /**
     * {@Inheritdoc}
     */
    public function setDefaultSchema(string $schemaName): void
    {
        $this->setHttpHeader('Default-Schema', $schemaName);
    }

    /**
     * @return string
     */
    private function nextServer(): string
    {
        $server = $this->availableServers[0];
        $this->roundRobin();

        return $server;
    }

    /**
     * Very simple round-robin implementation
     * Pops the first item of the availableServers array and appends it at the end.
     *
     * @return void
     */
    private function roundRobin(): void
    {
        $this->availableServers[] = array_shift($this->availableServers);
    }

    /**
     * @param string $server
     */
    private function dropServer(string $server): void
    {
        if (($idx = array_search($server, $this->availableServers, false)) !== false) {
            unset($this->availableServers[$idx]);
        }
    }

    /**
     * @param ConnectException $exception
     *
     * @throws \GuzzleHttp\Exception\ConnectException
     */
    private function raiseIfNoMoreServers(ConnectException $exception): void
    {
        if (count($this->availableServers) === 0) {
            throw new ConnectException(
                sprintf('No more servers available, exception from last server: %s', $exception->getMessage()),
                $exception->getRequest()
            );
        }
    }
}
