<?php
/**
 * @author Antoine Hedgcock
 */

namespace CrateIntegrationTest\PDO;

use Crate\CrateConst;
use Crate\PDO\PDO;
use PHPUnit_Framework_TestCase;

/**
 * Integration tests for {@see \Crate\PDO\PDO}
 *
 * @coversNothing
 *
 * @group integration
 */
class PDOTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PDO
     */
    protected $pdo;

    protected function setUp()
    {
        $this->pdo = new PDO('http://localhost:4200/_sql', null, null, []);
        $this->pdo->query('CREATE TABLE test_table (id INTEGER PRIMARY KEY, name string)');
    }

    protected function tearDown()
    {
        $this->pdo->query('DROP TABLE test_table');
    }

    protected function insertRow($count = 1)
    {
        for ($i = 0; $i <= $count; $i++) {
            $this->pdo->exec(sprintf("INSERT INTO test_table VALUES (%d, 'hello world')", $i));
        }

        $this->pdo->query('refresh table test_table');
    }

    public function testWithInvalidSQL()
    {
        $statement = $this->pdo->prepare('bogus sql');
        $statement->execute();

        $this->assertEquals(4000, $statement->errorCode());

        list ($ansiSQLError, $driverError, $driverMessage) = $statement->errorInfo();

        $this->assertEquals(42000, $ansiSQLError);
        $this->assertEquals(CrateConst::ERR_INVALID_SQL, $driverError);
        $this->assertEquals('SQLActionException[line 1:1: no viable alternative at input \'bogus\']', $driverMessage);
    }

    public function testSimple()
    {
        $statement = $this->pdo->prepare('SELECT * FROM test_table');
        $statement->execute();
    }

    public function testDelete()
    {
        $this->insertRow(3);

        $statement = $this->pdo->prepare('DELETE FROM test_table WHERE id = 1');

        $this->assertTrue($statement->execute());
        $this->assertEquals(1, $statement->rowCount());
    }
}
