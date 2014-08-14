<?php
/**
 * @author Antoine Hedgcock
 */

namespace CrateIntegrationTest\PDO;

use Crate\Stdlib\CrateConst;

/**
 * Integration tests for {@see \Crate\PDO\PDO}
 *
 * @coversNothing
 *
 * @group integration
 */
class PDOTest extends AbstractIntegrationTest
{
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

    public function testDelete()
    {
        $this->insertRows(1);

        $statement = $this->pdo->prepare('DELETE FROM test_table WHERE id = 1');

        $this->assertTrue($statement->execute());
        $this->assertEquals(1, $statement->rowCount());
    }

    public function testDeleteWithMultipleAffectedRows()
    {
        $this->insertRows(5);

        $statement = $this->pdo->prepare('DELETE FROM test_table WHERE id > 1');

        $this->assertTrue($statement->execute());
        $this->assertEquals(-1, $statement->rowCount());
    }
}
