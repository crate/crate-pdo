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

interface PDOInterfacePhp8
{
    public function prepare(string $statement, array $options = []);
    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollback(): bool;
    public function inTransaction(): bool;
    public function exec($statement);
    public function query(?string $query = null, ?int $fetchMode = null, mixed ...$fetchModeArgs);
    public function lastInsertId(?string $name = null): string;
    public function errorCode();
    public function errorInfo();
    public function setAttribute($attribute, $value);
    public function getAttribute($attribute);
    public function getServerVersion();
    public function getServerInfo();
    public static function getAvailableDrivers();
}
