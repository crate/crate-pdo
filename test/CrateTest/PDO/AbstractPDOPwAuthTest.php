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

namespace CrateTest\PDO;

use Crate\PDO\PDOCrateDB;
use PHPUnit\Framework\TestCase;

abstract class AbstractPDOPwAuthTest extends TestCase
{
    /**
     * @var PDOCrateDB
     */
    protected $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDOCrateDB('crate:localhost:4200', 'crate', 'crate');
        $this->pdo->setAttribute(PDOCrateDB::CRATE_ATTR_SSL_MODE, PDOCrateDB::CRATE_ATTR_SSL_MODE_ENABLED_BUT_WITHOUT_HOST_VERIFICATION);

        $usr = $this->pdo->prepare("CREATE USER test_user WITH (password = 'pwd')");

        $usr->execute();

        $priv = "GRANT ALL PRIVILEGES TO test_user;";
        $this->pdo->query($priv);
    }

    protected function tearDown(): void
    {
        $del = "DROP USER IF EXISTS test_user";
        $this->pdo->query($del);
    }
}
