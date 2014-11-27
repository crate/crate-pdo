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

use Crate\PDO\ArtaxExt\ClientInterface;
use Crate\PDO\PDO;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Tests for {@see \Crate\PDO\PDO}
 *
 * @coversDefaultClass \Crate\PDO\PDO
 * @covers ::<!public>
 *
 * @group unit
 */
class PDOTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ClientInterface
     */
    protected $client;

    protected function setUp()
    {
        $this->client = $this->getMock('Crate\PDO\ArtaxExt\ClientInterface');

        $this->pdo = new PDO('crate:localhost:1234', null, null, []);

        $reflection = new ReflectionClass('Crate\PDO\PDO');

        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->pdo, $this->client);
    }

    /**
     * @covers ::__construct
     */
    public function testInstantiation()
    {
        $pdo = new PDO('crate:localhost:1234', null, null, []);

        $this->assertInstanceOf('Crate\PDO\PDO', $pdo);
        $this->assertInstanceOf('PDO', $pdo);
    }

    /**
     * @covers ::__construct
     */
    public function testInstantiationWithTraversableOptions()
    {
        $pdo = new PDO('crate:localhost:1234', null, null, new \ArrayObject([PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]));
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(PDO::ATTR_ERRMODE));
    }

    /**
     * @covers ::__construct
     */
    public function testInstantiationWithInvalidOptions()
    {
        $this->setExpectedException('Crate\Stdlib\Exception\InvalidArgumentException');

        new PDO('crate:localhost:1234', null, null, 'a invalid value');
    }

    /**
     * @covers ::getAttribute
     */
    public function testGetAttributeWithInvalidAttribute()
    {
        $this->setExpectedException('Crate\PDO\Exception\PDOException');
        $this->pdo->getAttribute('I DONT EXIST');
    }

    /**
     * @covers ::setAttribute
     */
    public function testSetAttributeWithInvalidAttribute()
    {
        $this->setExpectedException('Crate\PDO\Exception\PDOException');
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
     */
    public function testGetVersion()
    {
        $this->assertEquals(PDO::VERSION, $this->pdo->getAttribute(PDO::ATTR_CLIENT_VERSION));
    }

    /**
     * @covers ::getAttribute
     */
    public function testGetDriverName()
    {
        $this->assertEquals(PDO::DRIVER_NAME, $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
    }

    public function testGetStatementClass()
    {
        $this->assertEquals(['Crate\PDO\PDOStatement'], $this->pdo->getAttribute(PDO::ATTR_STATEMENT_CLASS));
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
    public function testGetAndSetTimeout()
    {
        $timeout = 3;

        $this->client
            ->expects($this->once())
            ->method('setTimeout')
            ->with($timeout);

        $this->assertEquals(5, $this->pdo->getAttribute(PDO::ATTR_TIMEOUT));

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
    }

    public function quoteExceptionProvider()
    {
        return [
            [PDO::PARAM_LOB, 'Crate\PDO\Exception\PDOException', 'This is not supported by crate.io'],
            [PDO::PARAM_STR, 'Crate\PDO\Exception\PDOException', 'This is not supported, please use prepared statements.'],
            [120, 'Crate\PDO\Exception\InvalidArgumentException', 'Unknown param type'],
        ];
    }

    /**
     * @dataProvider quoteExceptionProvider
     * @covers ::quote
     *
     * @param int $paramType
     * @param string $message
     */
    public function testQuoteWithExpectedException($paramType, $exception, $message)
    {
        $this->setExpectedException($exception, $message);
        $this->pdo->quote('helloWorld', $paramType);
    }

    /**
     * @covers ::prepare
     */
    public function testPrepareReturnsAPDOStatement()
    {
        $statement = $this->pdo->prepare('SELECT * FROM tweets');
        $this->assertInstanceOf('Crate\PDO\PDOStatement', $statement);
    }

    /**
     * @covers ::getAvailableDrivers
     */
    public function testAvailableDriversContainsCrate()
    {
        $this->assertContains('crate', PDO::getAvailableDrivers());
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
        $this->setExpectedException('Crate\PDO\Exception\UnsupportedException');
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
        $this->setExpectedException('Crate\PDO\Exception\UnsupportedException');
        $this->pdo->lastInsertId();
    }
}
