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
 * @coversDefaultClass \Crate\PDO\PDO
 *
 * @group integration
 */
class PDOTest extends PHPUnit_Framework_TestCase
{
    public function testWithInvalidSQL()
    {
        $pdo = new PDO('http://localhost:4200/_sql', null, null, []);

        $statement = $pdo->prepare('bogus sql');
        $statement->execute();

        $this->assertEquals(4000, $statement->errorCode());

        list ($ansiSQLError, $driverError, $driverMessage) = $statement->errorInfo();

        $this->assertEquals(42000, $ansiSQLError);
        $this->assertEquals(CrateConst::ERR_INVALID_SQL, $driverError);
        $this->assertEquals('SQLActionException[line 1:1: no viable alternative at input \'bogus\']', $driverMessage);
    }

    /**
     * This is just a test used during development
     */
    public function testSimple()
    {
        $pdo = new PDO('http://localhost:4200/_sql', null, null, []);

        $statement = $pdo->prepare('SELECT * FROM tweets LIMIT 2');
        $statement->execute();

        foreach ($statement as $row) {
            echo 'test';
            print_r($row);
        }
    }
}
