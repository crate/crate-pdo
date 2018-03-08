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

namespace Crate\PDO\Http;

use Crate\PDO\Exception\RuntimeException;
use Crate\PDO\PDO;
use Crate\Stdlib\Collection;
use Crate\Stdlib\CollectionInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\RequestOptions;

final class ServerPool implements ServerInterface
{
    private const DEFAULT_SERVER = 'localhost:4200';

    /**
     * @var string[]
     */
    private $availableServers  = [];
    private $lastVisitedServer = 0;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * Client constructor.
     *
     * @param PDO   $pdo
     * @param array $servers
     */
    public function __construct(PDO $pdo, array $servers)
    {
        if (\count($servers) === 0) {
            $servers = [self::DEFAULT_SERVER];
        }

        // micro optimization so we don't always hit the same server first
        shuffle($servers);

        foreach ($servers as $server) {
            $this->availableServers[] = $server;
        }

        $this->pdo        = $pdo;
        $this->httpClient = new Client();
    }

    /**
     * {@Inheritdoc}
     * @throws \GuzzleHttp\Exception\ConnectException
     */
    public function execute(string $query, array $parameters): CollectionInterface
    {
        $numServers = count($this->availableServers) - 1;

        for ($i = 0; $i <= $numServers; $i++) {

            // always get the first available server
            $server = $this->availableServers[0];

            // Move the selected server to the end of the stack
            $this->availableServers[] = array_shift($this->availableServers);

            $options = $this->getHttpOptions($server, $query, $parameters);

            try {
                $response     = $this->httpClient->request('POST', '/_sql', $options);
                $responseBody = json_decode((string) $response->getBody(), true);

                return new Collection(
                    $responseBody['rows'],
                    $responseBody['cols'],
                    $responseBody['duration'],
                    $responseBody['rowcount']
                );

            } catch (ConnectException $exception) {

                // Catch it before the BadResponseException but do nothing.
                continue;

            } catch (BadResponseException $exception) {

                $body = (string)$exception->getResponse()->getBody();
                $json = json_decode($body, true);

                if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException(sprintf('Server returned non-JSON response: %s', $body), 0, $exception);
                }

                $errorCode    = $json['error']['code'];
                $errorMessage = $json['error']['message'];

                throw new RuntimeException($errorMessage, $errorCode, $exception);
            }
        }

        throw new ConnectException(
            sprintf('No more servers available, exception from last server: %s', $exception->getMessage()),
            $exception->getRequest(),
            $exception
        );
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
     * Determines the options to pass to guzzle for each request
     *
     * @param string $server
     * @param string $query
     * @param array  $parameters
     *
     * @return array
     */
    private function getHttpOptions(string $server, string $query, array $parameters): array
    {
        $sslMode = $this->pdo->getAttribute(PDO::CRATE_ATTR_SSL_MODE);

        $protocol = $sslMode === PDO::CRATE_ATTR_SSL_MODE_DISABLED ? 'http' : 'https';

        $options = [
            'base_uri'                      => sprintf('%s://%s', $protocol, $server),
            RequestOptions::TIMEOUT         => $this->pdo->getAttribute(PDO::ATTR_TIMEOUT),
            RequestOptions::CONNECT_TIMEOUT => $this->pdo->getAttribute(PDO::ATTR_TIMEOUT),
            RequestOptions::AUTH            => $this->pdo->getAttribute(PDO::CRATE_ATTR_HTTP_BASIC_AUTH) ?: null,
            RequestOptions::JSON            => [
                'stmt' => $query,
                'args' => $parameters,
            ],

            RequestOptions::HEADERS => [
                'Default-Schema' => $this->pdo->getAttribute(PDO::CRATE_ATTR_DEFAULT_SCHEMA),
            ],
        ];

        if ($sslMode === PDO::CRATE_ATTR_SSL_MODE_ENABLED) {
            $options['verify'] = false;
        }

        $ca         = $this->pdo->getAttribute(PDO::CRATE_ATTR_SSL_CA);
        $caPassword = $this->pdo->getAttribute(PDO::CRATE_ATTR_SSL_CA_PASSWORD);

        if ($ca) {
            if ($caPassword) {
                $options[RequestOptions::VERIFY] = [$ca, $caPassword];
            } else {
                $options[RequestOptions::VERIFY] = $ca;
            }
        }

        $cert         = $this->pdo->getAttribute(PDO::CRATE_ATTR_SSL_CERT);
        $certPassword = $this->pdo->getAttribute(PDO::CRATE_ATTR_SSL_CERT_PASSWORD);

        if ($cert) {
            if ($certPassword) {
                $options[RequestOptions::CERT] = [$cert, $certPassword];
            } else {
                $options[RequestOptions::CERT] = $cert;
            }
        }

        $key         = $this->pdo->getAttribute(PDO::CRATE_ATTR_SSL_KEY);
        $keyPassword = $this->pdo->getAttribute(PDO::CRATE_ATTR_SSL_KEY_PASSWORD);

        if ($key) {
            if ($keyPassword) {
                $options[RequestOptions::SSL_KEY] = [$key, $keyPassword];
            } else {
                $options[RequestOptions::SSL_KEY] = $key;
            }
        }

        return $options;
    }
}
