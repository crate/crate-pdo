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
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

final class Server implements ServerInterface
{

    private const PROTOCOL = 'http';
    private const SQL_PATH = '/_sql';

    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var array
     */
    private $opts = [
        'headers' => [],
    ];

    /**
     * @param string $uri
     * @param array  $options
     */
    public function __construct(string $uri, array $options)
    {
        $this->client = new HttpClient([
            'base_uri' => sprintf('%s://%s%s', self::PROTOCOL, $uri, self::SQL_PATH),
        ]);

        $this->opts = array_merge($this->opts, $options);
    }

    public function setTimeout(int $timeout): void
    {
        $this->opts['timeout'] = (float)$timeout;
    }

    public function setHttpBasicAuth(string $username, string $password): void
    {
        $this->opts['auth'] = [$username, $password];
    }

    public function setHttpHeader(string $name, string $value): void
    {
        $this->opts['headers'][$name] = $value;
    }

    public function getServerInfo(): array
    {
        return [
            'serverVersion' => $this->getServerVersion(),
        ];
    }

    public function getServerVersion(): string
    {
        $result = $this->execute("select version['number'] from sys.nodes limit 1");

        if (count($result->getRows()) !== 1) {
            throw new RuntimeException('Failed to determine server version');
        }

        return $result->getRows()[0][0];
    }

    /**
     * Execute a HTTP/1.1 POST request with JSON body
     *
     * @param string $query
     * @param array  $parameters
     *
     * @return CollectionInterface
     */
    public function execute(string $query, array $parameters = []): CollectionInterface
    {
        $body = array_merge($this->opts, ['json' => [
            'stmt' => $query,
            'args' => $parameters,
        ]]);

        $response = $this->client->post(null, $body);

        $responseBody = json_decode($response->getBody(), true);

        return new Collection(
            $responseBody['rows'],
            $responseBody['cols'],
            $responseBody['duration'],
            $responseBody['rowcount']
        );
    }
}
