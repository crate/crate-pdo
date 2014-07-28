<?php
// @todo license headers to be added

namespace CrateTest\PDO;

use Crate\PDO\PDOStatement;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \Crate\PDO\PDOStatement}
 *
 * @covers \Crate\PDO\PDOStatement
 */
class PDOStatementTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiaton()
    {
        $pdoStatement = new PDOStatement();

        $this->assertInstanceOf('Crate\PDO\PDOStatement', $pdoStatement);
        $this->assertInstanceOf('PDOStatement', $pdoStatement);
    }
}
