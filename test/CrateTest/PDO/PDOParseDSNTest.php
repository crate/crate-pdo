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

use PHPUnit_Framework_TestCase;
use ReflectionMethod;

class PDOParseDSNTest extends PHPUnit_Framework_TestCase
{
    private function parseDSN($dsn)
    {
        $method = new ReflectionMethod('Crate\PDO\PDO', 'parseDSN');
        $method->setAccessible(true);

        return $method->invoke(null, $dsn);
    }

    public function testParseDSNSingleHost()
    {
        $dsn = 'crate:localhost:4200';
        $servers = $this->parseDSN($dsn);

        $this->assertEquals(1, count($servers));
        $this->assertEquals('localhost:4200', $servers[0]);
    }

    public function testParseDSNMissingName()
    {
        $dsn = 'localhost:4200';

        $this->setExpectedException('Crate\PDO\Exception\PDOException', sprintf('Invalid DSN %s', $dsn));
        $this->parseDSN($dsn);
    }

    public function testParseDSNEmpty()
    {
        $this->setExpectedException('Crate\PDO\Exception\PDOException', sprintf('Invalid DSN %s', ''));
        $this->parseDSN('');
    }

    public function testParseDSNInvalid()
    {
        $dsn = 'crate:localhost,demo.crate.io';

        $this->setExpectedException('Crate\PDO\Exception\PDOException', sprintf('Invalid DSN %s', $dsn));
        $this->parseDSN($dsn);
    }

}
