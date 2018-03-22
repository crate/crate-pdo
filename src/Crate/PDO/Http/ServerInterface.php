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

use Crate\PDO\PDOInterface;
use Crate\Stdlib\CollectionInterface;

interface ServerInterface
{
    /**
     * Update the server pool configuration
     *
     * This is called on the initial creation of the server pool and when an attribute get updates
     *
     * @param PDOInterface $PDO
     *
     * @return void
     */
    public function configure(PDOInterface $PDO): void;

    /**
     * Execute the PDOStatement and return the response from server
     * wrapped inside a Collection
     *
     * @param string $queryString
     * @param array  $parameters
     *
     * @return CollectionInterface
     */
    public function execute(string $queryString, array $parameters): CollectionInterface;

    /**
     * @return array
     */
    public function getServerInfo(): array;

    /**
     * @return string
     */
    public function getServerVersion(): string;
}
