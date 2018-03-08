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
use Crate\PDO\PDO;
use Crate\Stdlib\Collection;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ServerPoolTest extends TestCase
{
    /**
     * @var PDO
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

    protected function setUp()
    {
        $this->pdo        = new PDO('crate:localhost:4200');
        $this->serverPool = new ServerPool($this->pdo, ['localhost:4200', 'localhost:4200']);

        $this->client = $this->createMock(ClientInterface::class);

        $prop = (new \ReflectionClass($this->serverPool))->getProperty('httpClient');
        $prop->setAccessible(true);
        $prop->setValue($this->serverPool, $this->client);
    }

    public function pdoOptions()
    {
        return [
            [
                [
                    PDO::ATTR_TIMEOUT               => 10,
                    PDO::CRATE_ATTR_DEFAULT_SCHEMA  => 'default',
                    PDO::CRATE_ATTR_HTTP_BASIC_AUTH => ['foo', 'bar'],
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
                    PDO::CRATE_ATTR_SSL_MODE => PDO::CRATE_ATTR_SSL_MODE_ENABLED,
                ],
                [
                    RequestOptions::VERIFY => false,
                    'base_uri'             => 'https://localhost:4200',
                ],
            ],
            [
                [
                    PDO::CRATE_ATTR_SSL_MODE => PDO::CRATE_ATTR_SSL_MODE_REQUIRED,
                ],
                [
                    'base_uri' => 'https://localhost:4200',
                ],
            ],
            [
                [
                    PDO::CRATE_ATTR_SSL_MODE => PDO::CRATE_ATTR_SSL_MODE_REQUIRED,
                    PDO::CRATE_ATTR_SSL_CA   => 'foo.pem',
                ],
                [
                    'base_uri'             => 'https://localhost:4200',
                    RequestOptions::VERIFY => 'foo.pem',
                ],
            ],
            [
                [
                    PDO::CRATE_ATTR_SSL_MODE        => PDO::CRATE_ATTR_SSL_MODE_REQUIRED,
                    PDO::CRATE_ATTR_SSL_CA          => 'foo.pem',
                    PDO::CRATE_ATTR_SSL_CA_PASSWORD => 'foo',
                ],
                [
                    'base_uri'             => 'https://localhost:4200',
                    RequestOptions::VERIFY => ['foo.pem', 'foo'],
                ],
            ],
            [
                [
                    PDO::CRATE_ATTR_SSL_MODE => PDO::CRATE_ATTR_SSL_MODE_REQUIRED,
                    PDO::CRATE_ATTR_SSL_KEY  => 'foo.pem',
                ],
                [
                    'base_uri'              => 'https://localhost:4200',
                    RequestOptions::SSL_KEY => 'foo.pem',
                ],
            ],
            [
                [
                    PDO::CRATE_ATTR_SSL_MODE         => PDO::CRATE_ATTR_SSL_MODE_REQUIRED,
                    PDO::CRATE_ATTR_SSL_KEY          => 'foo.pem',
                    PDO::CRATE_ATTR_SSL_KEY_PASSWORD => 'foo',
                ],
                [
                    'base_uri'              => 'https://localhost:4200',
                    RequestOptions::SSL_KEY => ['foo.pem', 'foo'],
                ],
            ],
            [
                [
                    PDO::CRATE_ATTR_SSL_MODE => PDO::CRATE_ATTR_SSL_MODE_REQUIRED,
                    PDO::CRATE_ATTR_SSL_CERT => 'foo.pem',
                ],
                [
                    'base_uri'           => 'https://localhost:4200',
                    RequestOptions::CERT => 'foo.pem',
                ],
            ],
            [
                [
                    PDO::CRATE_ATTR_SSL_MODE          => PDO::CRATE_ATTR_SSL_MODE_REQUIRED,
                    PDO::CRATE_ATTR_SSL_CERT          => 'foo.pem',
                    PDO::CRATE_ATTR_SSL_CERT_PASSWORD => 'foo',
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

        $this->serverPool->execute('query', []);
    }

    public function testExecuteWithNoRespondingServers()
    {
        $this->expectException(ConnectException::class);

        $this->client
            ->expects($this->any())
            ->method('request')
            ->willThrowException(new ConnectException('helloWorld', new Request('post', 'localhost')));

        $this->serverPool->execute('helloWorld', []);
    }

    public function testExecuteWithFirstFailing()
    {
        $body = json_encode(['rows' => [], 'cols' => [], 'duration' => 0, 'rowcount' => 0]);

        $this->client
            ->expects($this->at(0))
            ->method('request')
            ->willThrowException(new ConnectException('helloWorld', new Request('post', 'localhost')));

        $this->client
            ->expects($this->at(1))
            ->method('request')
            ->willReturn(new Response(200, [], $body));

        $this->assertInstanceOf(Collection::class, $this->serverPool->execute('helloWorld', []));
    }

    public function testWithBadRequest()
    {
        $this->expectException(RuntimeException::class);

        $body = json_encode(['error' => ['code' => 1337, 'message' => 'invalid sql, u fool.']]);

        $request  = new Request('post', 'localhost');
        $response = new Response(400, [], $body);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new BadResponseException('error', $request, $response));

        $this->serverPool->execute('helloWorld', []);
    }
}
