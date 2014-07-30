<?php
// @todo license headers to be added

namespace CrateTest\PDO;

use Artax\Client;
use Crate\PDO\ArtaxExt\Request;
use Crate\PDO\PDOStatement;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \Crate\PDO\PDOStatement}
 *
 * @coverDefaultClass \Crate\PDO\PDOStatement
 *
 * @group unit
 * @group statement
 */
class PDOStatementTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Client|PHPUnit_Framework_MockObject_MockObject
     */
    protected $client;

    /**
     * @var Request|PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var PDOStatement
     */
    protected $statement;

    protected function setUp()
    {
        $this->client  = $this->getMock('Artax\Client');
        $this->request = $this->getMock('Crate\PDO\ArtaxExt\Request');

        $this->statement = new PDOStatement($this->client, $this->request);
    }

    /**
     * @covers ::__construct
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Crate\PDO\PDOStatement', $this->statement);
        $this->assertInstanceOf('PDOStatement', $this->statement);
    }

    /**
     * @covers ::closeCursor
     */
    public function testCloseCursorThrowsUnsupportedException()
    {
        $this->setExpectedException('Crate\PDO\Exception\UnsupportedException');
        $this->statement->closeCursor();
    }
}
