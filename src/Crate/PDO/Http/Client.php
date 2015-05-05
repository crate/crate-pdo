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

use GuzzleHttp\Client as HttpClient;
use Crate\PDO\Exception\RuntimeException;
use Crate\PDO\Exception\UnsupportedException;
use Crate\Stdlib\Collection;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ParseException;

class Client implements ClientInterface
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @param string $uri
     * @param array  $options
     */
    public function __construct($uri, array $options)
    {
        $this->client = new HttpClient(['base_url' => $uri] + $options);
    }

    /**
     * {@Inheritdoc}
     */
    public function execute($query, array $parameters)
    {
        $body = [
            'stmt' => $query,
            'args' => $parameters
        ];

        try {
            $response     = $this->client->post(null, ['json' => $body]);
            $responseBody = $response->json();

            return new Collection(
                $responseBody['rows'],
                $responseBody['cols'],
                $responseBody['duration'],
                $responseBody['rowcount']
            );

        } catch (BadResponseException $exception) {

            try {

                $json = $exception->getResponse()->json();

                $errorCode    = $json['error']['code'];
                $errorMessage = $json['error']['message'];

                throw new RuntimeException($errorMessage, $errorCode);

            } catch (ParseException $e) {
                throw new RuntimeException('Unparsable response from server', 0, $exception);
            }
        }
    }

    /**
     * {@Inheritdoc}
     */
    public function getServerInfo()
    {
        throw new UnsupportedException('Not yet implemented');
    }

    /**
     * {@Inheritdoc}
     */
    public function getServerVersion()
    {
        throw new UnsupportedException('Not yet implemented');
    }

    /**
     * {@Inheritdoc}
     */
    public function setTimeout($timeout)
    {
        $this->client->setDefaultOption('timeout', (float) $timeout);
    }

    /**
     * {@Inheritdoc}
     */
    public function setHttpBasicAuth($username, $passwd)
    {
        $this->client->setDefaultOption('auth', [$username, $passwd]);
    }
}
