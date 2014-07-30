<?php
/**
 * @author Antoine Hedgcock
 */

namespace CrateTest\PDO\Exception;

use Crate\PDO\Exception\PDOException;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \Crate\PDO\Exception\PDOException}
 *
 * @coverDefaultClass \Create\PDO\Exception\PDOException
 *
 * @group unit
 */
class PDOExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $exception = new PDOException();

        $this->assertInstanceOf('PDOException', $exception);
        $this->assertInstanceOf('Crate\PDO\Exception\ExceptionInterface', $exception);
    }
}
