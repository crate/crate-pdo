<?php
// @todo license headers to be added

namespace CrateTest\PDO;

use Crate\PDO\Exception\UnsupportedException;
use Crate\PDO\PDO;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \Crate\PDO\PDO}
 *
 * @coverDefaultClass \Crate\PDO\PDO
 *
 * @group unit
 */
class PDOTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PDO
     */
    protected $pdo;

    protected function setUp()
    {
        $this->pdo = new PDO('http://localhost:8080', null, null, []);
    }

    /**
     * @covers ::__construct
     */
    public function testInstantiation()
    {
        $pdo = new PDO('http://localhost:1234/', null, null, []);

        $this->assertInstanceOf('Crate\PDO\PDO', $pdo);
        $this->assertInstanceOf('PDO', $pdo);
    }

    /**
     * @covers ::beginTransaction
     */
    public function testBeginTransactionThrowsUnsupportedException()
    {
        $this->setExpectedException(UnsupportedException::class);
        $this->pdo->beginTransaction();
    }

    /**
     * @covers ::commit
     */
    public function testCommitThrowsUnsupportedException()
    {
        $this->setExpectedException(UnsupportedException::class);
        $this->pdo->commit();
    }

    /**
     * @covers ::rollback
     */
    public function testRollbackThrowsUnsupportedException()
    {
        $this->setExpectedException(UnsupportedException::class);
        $this->pdo->rollBack();
    }

    /**
     * @covers ::inTransaction
     */
    public function testInTransactionThrowsUnsupportedException()
    {
        $this->setExpectedException(UnsupportedException::class);
        $this->pdo->inTransaction();
    }

    /**
     * @covers ::lastInsertId
     */
    public function testLastInsertIdThrowsUnsupportedException()
    {
        $this->setExpectedException(UnsupportedException::class);
        $this->pdo->lastInsertId();
    }
}
