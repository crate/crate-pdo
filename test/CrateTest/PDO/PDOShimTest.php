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

use Crate\PDO\PDO;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \Crate\PDO\PDO}
 *
 * @coversDefaultClass \Crate\PDO\PDO
 * @covers ::<!public>
 *
 * @group unit
 */
class PDOShimTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testInstantiation()
    {
        $pdo = new PDO('crate:localhost:1234', null, null, []);

        $this->assertInstanceOf(PDO::class, $pdo);
        $this->assertInstanceOf('PDO', $pdo);
    }

    public function testDeprecation()
    {
        /**
         * Test whether an appropriate deprecation warning is raised, that `Crate\PDO\PDO` is going to be removed.
         *
         * https://nerdpress.org/2022/04/04/testing-deprecations-with-phpunit/
         * https://codeseekah.com/2023/03/01/testing-warnings-in-phpunit-9/
         * https://github.com/sebastianbergmann/phpunit/issues/5062
         */

        $errored = null;
        set_error_handler(function($errno, $errstr, ...$args) use (&$errored) {
            $errored = [$errno, $errstr, $args];
            restore_error_handler();
        });

        // Calling function which should emit a deprecation warning.
        new PDO('crate:localhost:1234', null, null, []);

        $this->assertNotNull($errored, 'No warning has been triggered');
        [$errno, $errstr, $args] = $errored;
        $this->assertEquals(
            E_USER_DEPRECATED, $errno,
            'E_USER_DEPRECATED has not been triggered');
        $this->assertStringStartsWith(
            'The API interface `Crate\PDO\PDO` is deprecated', $errstr,
            'Deprecation warning about `Crate\PDO\PDO` not issued');

    }
}
