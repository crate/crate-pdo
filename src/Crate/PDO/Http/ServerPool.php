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
use Crate\PDO\PDOInterface;
use Crate\Stdlib\Collection;
use Crate\Stdlib\CollectionInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\RequestOptions;

/**
 * Class ServerPool
 *
 * Very basic round robin implementation
 */
final class ServerPool implements ServerInterface
{
    private const DEFAULT_SERVER = 'localhost:4200';

    /**
     * @var string
     */
    private $protocol = 'http';

    /**
     * @var array
     */
    private $httpOptions = [];

    /**
     * @var string[]
     */
    private $availableServers = [];

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * Client constructor.
     *
     * @param array                $servers
     * @param ClientInterface|null $client
     */
    public function __construct(array $servers, ClientInterface $client = null)
    {
        if (\count($servers) === 0) {
            $servers = [self::DEFAULT_SERVER];
        }

        // micro optimization so we don't always hit the same server first
        shuffle($servers);

        foreach ($servers as $server) {
            $this->availableServers[] = $server;
        }

        $this->httpClient = $client ?: new Client();
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

            $options = array_merge($this->httpOptions, [
                'base_uri' => sprintf('%s://%s', $this->protocol, $server),
                'json'     => [
                    'stmt' => $query,
                    'args' => $parameters,
                ],
            ]);

            try {
                $response     = $this->httpClient->request('POST', '/_sql', $options);
                $responseBody = json_decode((string)$response->getBody(), true);

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
     * Reconfigure the the server pool based on the attributes in PDO
     *
     * @param PDOInterface $pdo
     */
    public function configure(PDOInterface $pdo): void
    {
        $sslMode = $pdo->getAttribute(PDO::CRATE_ATTR_SSL_MODE);

        $protocol = $sslMode === PDO::CRATE_ATTR_SSL_MODE_DISABLED ? 'http' : 'https';

        $options = [
            RequestOptions::TIMEOUT         => $pdo->getAttribute(PDO::ATTR_TIMEOUT),
            RequestOptions::CONNECT_TIMEOUT => $pdo->getAttribute(PDO::ATTR_TIMEOUT),
            RequestOptions::AUTH            => $pdo->getAttribute(PDO::CRATE_ATTR_HTTP_BASIC_AUTH) ?: null,
            RequestOptions::HEADERS         => [
                'Default-Schema' => $pdo->getAttribute(PDO::CRATE_ATTR_DEFAULT_SCHEMA),
            ],
        ];

        if ($sslMode === PDO::CRATE_ATTR_SSL_MODE_ENABLED_BUT_WITHOUT_HOST_VERIFICATION) {
            $options['verify'] = false;
        }

        $ca         = $pdo->getAttribute(PDO::CRATE_ATTR_SSL_CA_PATH);
        $caPassword = $pdo->getAttribute(PDO::CRATE_ATTR_SSL_CA_PASSWORD);

        if ($ca) {
            if ($caPassword) {
                $options[RequestOptions::VERIFY] = [$ca, $caPassword];
            } else {
                $options[RequestOptions::VERIFY] = $ca;
            }
        }

        $cert         = $pdo->getAttribute(PDO::CRATE_ATTR_SSL_CERT_PATH);
        $certPassword = $pdo->getAttribute(PDO::CRATE_ATTR_SSL_CERT_PASSWORD);

        if ($cert) {
            if ($certPassword) {
                $options[RequestOptions::CERT] = [$cert, $certPassword];
            } else {
                $options[RequestOptions::CERT] = $cert;
            }
        }

        $key         = $pdo->getAttribute(PDO::CRATE_ATTR_SSL_KEY_PATH);
        $keyPassword = $pdo->getAttribute(PDO::CRATE_ATTR_SSL_KEY_PASSWORD);

        if ($key) {
            if ($keyPassword) {
                $options[RequestOptions::SSL_KEY] = [$key, $keyPassword];
            } else {
                $options[RequestOptions::SSL_KEY] = $key;
            }
        }

        $this->protocol    = $protocol;
        $this->httpOptions = $options;
    }
}
