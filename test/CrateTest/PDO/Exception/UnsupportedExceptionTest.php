<?php
/**
 * @author Antoine Hedgcock
 */

namespace CrateTest\PDO\Exception;

use Crate\PDO\Exception\UnsupportedException;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \Crate\PDO\Exception\UnsupportedException}
 *
 * @coversDefaultClass \Crate\PDO\Exception\UnsupportedException
 */
class UnsupportedExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $exception = new UnsupportedException();

        $this->assertInstanceOf('Crate\PDO\Exception\PDOException', $exception);

        $this->assertEquals('Unsupported functionality', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }
}
