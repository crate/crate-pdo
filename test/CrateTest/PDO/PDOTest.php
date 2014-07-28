<?php
// @todo license headers to be added

namespace CrateTest\PDO;

use Crate\PDO\PDO;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \Crate\PDO\PDO}
 *
 * @covers \Crate\PDO\PDO
 */
class PDOTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiaton()
    {
        $pdo = new PDO('http://localhost:1234/', null, null, []);

        $this->assertInstanceOf('Crate\PDO\PDO', $pdo);
        $this->assertInstanceOf('PDO', $pdo);
    }
}
