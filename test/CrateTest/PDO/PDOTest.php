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

use Crate\PDO\Exception\InvalidArgumentException;
use Crate\PDO\Exception\PDOException;
use Crate\PDO\Exception\LogicException;
use Crate\PDO\Exception\UnsupportedException;
use Crate\PDO\Http\ServerInterface;
use Crate\PDO\PDOCrateDB;
use Crate\PDO\PDOStatement;
use Generator;
use PDO;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for {@see \Crate\PDO\PDOCrateDB}
 *
 * @coversDefaultClass \Crate\PDO\PDOCrateDB
 * @covers ::<!public>
 *
 * @group unit
 */
class PDOTest extends TestCase
{
    /**
     * @var PDOCrateDB
     */
    protected $pdo;

    /**
     * @var ServerInterface|MockObject
     */
    protected $server;

    protected function setUp(): void
    {
        $this->server = $this->getMockBuilder(ServerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pdo = new PDOCrateDB('crate:localhost:1234', null, null, []);
        $this->pdo->setServer($this->server);
    }

    /**
     * @covers ::__construct
     */
    public function testInstantiation()
    {
        $pdo = new PDOCrateDB('crate:localhost:1234', null, null, []);

        $this->assertInstanceOf(PDOCrateDB::class, $pdo);
        $this->assertInstanceOf('PDO', $pdo);
    }

    public function testInstantiationWithDefaultSchema()
    {
        $pdo = new PDOCrateDB('crate:localhost:1234/my_schema', null, null, []);

        $this->assertInstanceOf(PDOCrateDB::class, $pdo);
        $this->assertInstanceOf(\PDO::CLASS, $pdo);
    }

    /**
     * @covers ::__construct
     */
    public function testInstantiationWithTraversableOptions()
    {
        $pdo = new PDOCrateDB('crate:localhost:1234', null, null, new \ArrayObject([PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]));
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
    }

    /**
     * @covers ::__construct
     */
    public function testInstantiationWithHttpAuth()
    {
        $user   = 'crate';
        $passwd = 'secret';
        $pdo    = new PDOCrateDB('crate:localhost:44200', $user, $passwd, []);
        $this->assertEquals([$user, $passwd], $pdo->getAttribute(PDOCrateDB::CRATE_ATTR_HTTP_BASIC_AUTH));
    }

    /**
     * @covers ::getAttribute
     */
    public function testGetAttributeWithInvalidAttribute()
    {
        $this->expectException(PDOException::class);
        $this->pdo->getAttribute('I DONT EXIST');
    }

    /**
     * @covers ::setAttribute
     */
    public function testSetAttributeWithInvalidAttribute()
    {
        $this->expectException(PDOException::class);
        $this->pdo->setAttribute('I DONT EXIST', 'value');
    }

    /**
     * @covers ::getAttribute
     * @covers ::setAttribute
     */
    public function testGetAndSetDefaultFetchMode()
    {
        $this->assertEquals(PDO::FETCH_BOTH, $this->pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->assertEquals(PDO::FETCH_ASSOC, $this->pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
    }

    /**
     * @covers ::getAttribute
     * @covers ::setAttribute
     */
    public function testGetAndSetErrorMode()
    {
        $this->assertEquals(PDO::ERRMODE_SILENT, $this->pdo->getAttribute(PDO::ATTR_ERRMODE));
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $this->pdo->getAttribute(PDO::ATTR_ERRMODE));
    }

    /**
     * @covers ::getAttribute
     * @covers ::setAttribute
     */
    public function testGetAndSetStatementClass()
    {
        $this->assertEquals([PDOStatement::class], $this->pdo->getAttribute(PDO::ATTR_STATEMENT_CLASS));
        $this->pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, 'Doctrine\DBAL\Driver\PDO\Statement');
        $this->assertEquals(['Doctrine\DBAL\Driver\PDO\Statement'], $this->pdo->getAttribute(PDO::ATTR_STATEMENT_CLASS));
    }

    /**
     * @covers ::getAttribute
     */
    public function testGetVersion()
    {
        $this->assertEquals(PDOCrateDB::VERSION, $this->pdo->getAttribute(PDOCrateDB::ATTR_CLIENT_VERSION));
    }

    /**
     * @covers ::getAttribute
     */
    public function testGetDriverName()
    {
        $this->assertEquals(PDOCrateDB::DRIVER_NAME, $this->pdo->getAttribute(PDOCrateDB::ATTR_DRIVER_NAME));
    }

    public function testGetStatementClass()
    {
        $this->assertEquals([PDOStatement::class], $this->pdo->getAttribute(PDO::ATTR_STATEMENT_CLASS));
    }

    /**
     * @covers ::getAttribute
     */
    public function testPersistent()
    {
        $this->assertFalse($this->pdo->getAttribute(PDO::ATTR_PERSISTENT));
    }

    /**
     * @covers ::getAttribute
     */
    public function testPreFetch()
    {
        $this->assertFalse($this->pdo->getAttribute(PDO::ATTR_PREFETCH));
    }

    /**
     * @covers ::getAttribute
     */
    public function testAutoCommit()
    {
        $this->assertTrue($this->pdo->getAttribute(PDO::ATTR_AUTOCOMMIT));
    }

    /**
     * @covers ::getAttribute
     * @covers ::setAttribute
     */
    public function testGetAndSetDefaultSchema()
    {
        $this->assertEquals('doc', $this->pdo->getAttribute(PDOCrateDB::CRATE_ATTR_DEFAULT_SCHEMA));
        $this->pdo->setAttribute(PDOCrateDB::CRATE_ATTR_DEFAULT_SCHEMA, 'new');
        $this->assertEquals('new', $this->pdo->getAttribute(PDOCrateDB::CRATE_ATTR_DEFAULT_SCHEMA));
    }

    /**
     * @covers ::getAttribute
     * @covers ::setAttribute
     */
    public function testGetAndSetTimeout()
    {
        $timeout = 3;

        $this->assertEquals(0, $this->pdo->getAttribute(PDO::ATTR_TIMEOUT));

        $this->pdo->setAttribute(PDO::ATTR_TIMEOUT, $timeout);

        $this->assertEquals($timeout, $this->pdo->getAttribute(PDO::ATTR_TIMEOUT));
    }

    /**
     * @covers ::quote
     */
    public function testQuote()
    {
        $this->assertTrue($this->pdo->quote('1', PDO::PARAM_BOOL));
        $this->assertFalse($this->pdo->quote('0', PDO::PARAM_BOOL));

        $this->assertEquals(100, $this->pdo->quote('100', PDO::PARAM_INT));
        $this->assertNull($this->pdo->quote('helloWorld', PDO::PARAM_NULL));

        $this->assertEquals("Don''t bother", $this->pdo->quote("Don't bother", \PDO::PARAM_STR));
    }

    /**
     * @return array
     */
    public function quoteExceptionProvider()
    {
        return [
            [PDO::PARAM_LOB, PDOException::class, 'This is not supported by crate.io'],
            [120, InvalidArgumentException::class, 'Unknown param type'],
        ];
    }

    /**
     * @dataProvider quoteExceptionProvider
     * @covers ::quote
     *
     * @param int    $paramType
     * @param string $message
     */
    public function testQuoteWithExpectedException($paramType, $exception, $message)
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($message);

        $this->pdo->quote('helloWorld', $paramType);
    }

    /**
     * @covers ::prepare
     */
    public function testPrepareReturnsAPDOStatement()
    {
        $statement = $this->pdo->prepare('SELECT * FROM tweets');
        $this->assertInstanceOf(PDOStatement::class, $statement);
    }

    /**
     * @covers ::getAvailableDrivers
     */
    public function testAvailableDriversContainsCrate()
    {
        $this->assertContains('crate', PDOCrateDB::getAvailableDrivers());
    }

    /**
     * @covers ::beginTransaction
     */
    public function testBeginTransactionThrowsUnsupportedException()
    {
        $this->assertTrue($this->pdo->beginTransaction());
    }

    /**
     * @covers ::commit
     */
    public function testCommitThrowsUnsupportedException()
    {
        $this->assertTrue($this->pdo->commit());
    }

    /**
     * @covers ::rollback
     */
    public function testRollbackThrowsUnsupportedException()
    {
        $this->expectException(UnsupportedException::class);
        $this->pdo->rollBack();
    }

    /**
     * @covers ::inTransaction
     */
    public function testInTransactionThrowsUnsupportedException()
    {
        $this->assertFalse($this->pdo->inTransaction());
    }

    /**
     * @covers ::lastInsertId
     */
    public function testLastInsertIdThrowsUnsupportedException()
    {
        $this->expectException(UnsupportedException::class);
        $this->pdo->lastInsertId();
    }

    /**
     * PHP8 has a new PDOCrateDB query interface.
     * Not all things have been implemented yet.
     *
     * @requires PHP >= 8
     * @covers ::query
     *
     * @dataProvider fetchModeStyleProvider
     * @param $fetchMode
     */
    public function testNewPhp8PdoInterfaceQueryWithFetchMode($fetchMode)
    {
        $this->pdo->query("SELECT 1;", $fetchMode);
    }

    /**
     * Provide test function with a list of parameters.
     *
     * @return Generator
     */
    public function fetchModeStyleProvider()
    {
        return [
            [PDO::FETCH_ASSOC],
            [PDO::FETCH_NUM],
            [PDO::FETCH_BOTH],
            [PDO::FETCH_BOUND],
            [PDO::FETCH_NAMED],
        ];
    }

    public function testNewPhp8PdoInterfaceQueryWithInvalidFetchMode()
    {
        $this->expectException(UnsupportedException::class);
        $this->expectExceptionMessage('Invalid fetch mode specified');

        $this->pdo->query("SELECT 1;", 99);
    }

    public function testNewPhp8PdoInterfaceQueryWithInvalidFetchModeParams()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fetch mode does not allow any extra arguments');

        $this->pdo->query("SELECT 1;", PDO::FETCH_NAMED, 'foobar');
    }

    /**
     * Verify support for CrateDB bulk operations.
     * https://crate.io/docs/crate/reference/en/latest/interfaces/http.html#bulk-operations
     *
     * @covers ::query
     */
    public function testBulkMode()
    {
        $statement = $this->pdo->prepare("INSERT INTO foobar;", array("bulkMode" => true));

        // Enable accessing the private `options` property of the `PDOStatement` instance.
        $class = new ReflectionClass($statement);
        $options_p = $class->getProperty("options");
        $options_p->setAccessible(true);
        $options = $options_p->getValue($statement);

        // Verify bulk mode has been enabled.
        $this->assertEquals(true, $options["bulkMode"]);
    }

    public function testDeprecationPHP7()
    {
        /**
         * Test whether an appropriate deprecation warning is raised on PHP7.
         *
         * https://nerdpress.org/2022/04/04/testing-deprecations-with-phpunit/
         * https://codeseekah.com/2023/03/01/testing-warnings-in-phpunit-9/
         * https://github.com/sebastianbergmann/phpunit/issues/5062
         */

        if (PHP_VERSION_ID >= 80000) {
            $this->markTestSkipped('Test for PHP7 deprecation not applicable on PHP8');
        }

        $errored = null;
        set_error_handler(function($errno, $errstr, ...$args) use (&$errored) {
            $errored = [$errno, $errstr, $args];
            restore_error_handler();
        });

        // Calling function which should emit a deprecation warning.
        new PDOCrateDB('crate:localhost:1234', null, null, []);

        $this->assertNotNull($errored, 'No warning has been triggered');
        [$errno, $errstr, $args] = $errored;
        $this->assertEquals(E_USER_DEPRECATED, $errno, 'E_USER_DEPRECATED has not been triggered');
        $this->assertStringStartsWith('`crate/crate-pdo` will stop supporting PHP7', $errstr, 'PHP7 deprecation not issued');

    }

    public function testExec()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("The statement has not been executed yet");
        $this->pdo->exec("SELECT 1;");
    }

    public function testErrorCode()
    {
        $pdo = new PDOCrateDB('crate:localhost:1234', null, null, []);
        $this->assertEquals(0, $pdo->errorCode());
    }

    public function testErrorInfo()
    {
        $pdo = new PDOCrateDB('crate:localhost:1234', null, null, []);
        $this->assertEquals(["00000", null, null], $pdo->errorInfo());
    }

}
