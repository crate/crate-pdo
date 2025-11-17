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

namespace CrateTest\PDO\Http;

use Crate\PDO\Exception\RuntimeException;
use Crate\PDO\Http\ServerPool;
use Crate\PDO\PDOCrateDB;
use Crate\Stdlib\BulkResponse;
use Crate\Stdlib\Collection;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use PDO;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ServerPoolTest extends TestCase
{
    /**
     * @var PDOCrateDB
     */
    private $pdo;

    /**
     * @var Client|MockObject
     */
    private $client;

    /**
     * @var ServerPool
     */
    private $serverPool;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);

        $this->serverPool = new ServerPool(['localhost:4200', 'localhost:4200'], $this->client);

        $this->pdo = new PDOCrateDB('crate:localhost:4200');
        $this->pdo->setServer($this->serverPool);
    }

    public function pdoOptions()
    {
        return [
            [
                [
                    PDO::ATTR_TIMEOUT                      => 10,
                    PDOCrateDB::CRATE_ATTR_DEFAULT_SCHEMA  => 'default',
                    PDOCrateDB::CRATE_ATTR_HTTP_BASIC_AUTH => ['foo', 'bar'],
                ],
                [
                    RequestOptions::TIMEOUT         => 10,
                    RequestOptions::CONNECT_TIMEOUT => 10,
                    RequestOptions::AUTH            => ['foo', 'bar'],
                    RequestOptions::HEADERS         => [
                        'Default-Schema' => 'default',
                    ],
                ],
            ],
            [
                [
                    PDOCrateDB::CRATE_ATTR_SSL_MODE => PDOCrateDB::CRATE_ATTR_SSL_MODE_ENABLED_BUT_WITHOUT_HOST_VERIFICATION,
                ],
                [
                    RequestOptions::VERIFY => false,
                    'base_uri'             => 'https://localhost:4200',
                ],
            ],
            [
                [
                    PDOCrateDB::CRATE_ATTR_SSL_MODE => PDOCrateDB::CRATE_ATTR_SSL_MODE_REQUIRED,
                ],
                [
                    'base_uri' => 'https://localhost:4200',
                ],
            ],
            [
                [
                    PDOCrateDB::CRATE_ATTR_SSL_MODE    => PDOCrateDB::CRATE_ATTR_SSL_MODE_REQUIRED,
                    PDOCrateDB::CRATE_ATTR_SSL_CA_PATH => 'foo.pem',
                ],
                [
                    'base_uri'             => 'https://localhost:4200',
                    RequestOptions::VERIFY => 'foo.pem',
                ],
            ],
            [
                [
                    PDOCrateDB::CRATE_ATTR_SSL_MODE        => PDOCrateDB::CRATE_ATTR_SSL_MODE_REQUIRED,
                    PDOCrateDB::CRATE_ATTR_SSL_CA_PATH     => 'foo.pem',
                    PDOCrateDB::CRATE_ATTR_SSL_CA_PASSWORD => 'foo',
                ],
                [
                    'base_uri'             => 'https://localhost:4200',
                    RequestOptions::VERIFY => ['foo.pem', 'foo'],
                ],
            ],
            [
                [
                    PDOCrateDB::CRATE_ATTR_SSL_MODE     => PDOCrateDB::CRATE_ATTR_SSL_MODE_REQUIRED,
                    PDOCrateDB::CRATE_ATTR_SSL_KEY_PATH => 'foo.pem',
                ],
                [
                    'base_uri'              => 'https://localhost:4200',
                    RequestOptions::SSL_KEY => 'foo.pem',
                ],
            ],
            [
                [
                    PDOCrateDB::CRATE_ATTR_SSL_MODE         => PDOCrateDB::CRATE_ATTR_SSL_MODE_REQUIRED,
                    PDOCrateDB::CRATE_ATTR_SSL_KEY_PATH     => 'foo.pem',
                    PDOCrateDB::CRATE_ATTR_SSL_KEY_PASSWORD => 'foo',
                ],
                [
                    'base_uri'              => 'https://localhost:4200',
                    RequestOptions::SSL_KEY => ['foo.pem', 'foo'],
                ],
            ],
            [
                [
                    PDOCrateDB::CRATE_ATTR_SSL_MODE      => PDOCrateDB::CRATE_ATTR_SSL_MODE_REQUIRED,
                    PDOCrateDB::CRATE_ATTR_SSL_CERT_PATH => 'foo.pem',
                ],
                [
                    'base_uri'           => 'https://localhost:4200',
                    RequestOptions::CERT => 'foo.pem',
                ],
            ],
            [
                [
                    PDOCrateDB::CRATE_ATTR_SSL_MODE          => PDOCrateDB::CRATE_ATTR_SSL_MODE_REQUIRED,
                    PDOCrateDB::CRATE_ATTR_SSL_CERT_PATH     => 'foo.pem',
                    PDOCrateDB::CRATE_ATTR_SSL_CERT_PASSWORD => 'foo',
                ],
                [
                    'base_uri'           => 'https://localhost:4200',
                    RequestOptions::CERT => ['foo.pem', 'foo'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider pdoOptions
     *
     * @param array $options
     * @param array $expected
     */
    public function testGuzzleClientOptionTest(array $options, array $expected)
    {
        foreach ($options as $attr => $val) {
            $this->pdo->setAttribute($attr, $val);
        }

        $expectedWithDefaults = array_merge([
            RequestOptions::TIMEOUT         => 0.0,
            RequestOptions::CONNECT_TIMEOUT => 0.0,
            RequestOptions::JSON            => [
                'stmt' => 'query',
                'args' => [],
            ],

            RequestOptions::HEADERS => [
                'Default-Schema' => 'doc',
            ],

            RequestOptions::AUTH => null,
            'base_uri'           => 'http://localhost:4200',
        ], $expected);

        $body = json_encode(['rows' => [], 'cols' => [], 'duration' => 0, 'rowcount' => 0]);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/_sql', $expectedWithDefaults)
            ->willReturn(new Response(200, [], $body));

        $result = $this->serverPool->execute('query', []);

        $this->assertEquals(new Collection([], [], 0, 0), $result);

    }

    /**
     * Verify Guzzle client behavior when using CrateDB bulk operations.
     * https://crate.io/docs/crate/reference/en/latest/interfaces/http.html#bulk-operations
     *
     */
    public function testGuzzleClientOptionTestBulkMode()
    {
        $expectedWithDefaults = [
            RequestOptions::TIMEOUT         => 0.0,
            RequestOptions::CONNECT_TIMEOUT => 0.0,
            RequestOptions::JSON            => [
                'stmt' => 'query',
                'bulk_args' => [["foo", "bar"]],
            ],

            RequestOptions::HEADERS => [
                'Default-Schema' => 'doc',
            ],

            RequestOptions::AUTH => null,
            'base_uri'           => 'http://localhost:4200',
        ];

        $body = json_encode(['results' => [["foo", "bar"]], 'cols' => [], 'duration' => 0]);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('POST', '/_sql', $expectedWithDefaults)
            ->willReturn(new Response(200, [], $body));

        $result = $this->serverPool->executeBulk('query', [["foo", "bar"]]);

        $this->assertEquals(new BulkResponse([["foo", "bar"]], [], 0), $result);

    }

    public function testExecuteWithNoRespondingServers()
    {
        $this->client
            ->expects($this->any())
            ->method('request')
            ->willThrowException(new ConnectException('helloWorld', new Request('post', 'localhost')));

        $this->expectException(ConnectException::class);
        $this->expectExceptionMessage("No more servers available, exception from last server: helloWorld");

        $this->serverPool->execute('helloWorld', []);
    }

    public function testExecuteWithFirstFailing()
    {
        $body = json_encode(['rows' => [], 'cols' => [], 'duration' => 0, 'rowcount' => 0]);

        $this->client
            ->expects($this->exactly(2))
            ->method('request')
            ->will(self::onConsecutiveCalls(
                self::throwException(new ConnectException('helloWorld', new Request('post', 'localhost'))),
                new Response(200, [], $body)
            ));

        $result = $this->serverPool->execute('helloWorld', []);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(new Collection([], [], 0, 0), $result);
    }

    public function testWithBadRequest()
    {
        $body = json_encode(['error' => ['code' => 1337, 'message' => 'Invalid SQL statement']]);

        $request  = new Request('post', 'localhost');
        $response = new Response(400, [], $body);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new BadResponseException('error', $request, $response));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Invalid SQL statement");

        $this->serverPool->execute('helloWorld', []);
    }
}
