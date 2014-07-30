<?php
// @todo license headers to be added

namespace CrateTest\PDO;

use Crate\PDO\PDOStatement;
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
     * @var PDOStatement
     */
    protected $statement;

    protected function setUp()
    {
        $this->statement = new PDOStatement();
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