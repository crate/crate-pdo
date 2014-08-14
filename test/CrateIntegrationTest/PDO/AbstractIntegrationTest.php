<?php
/**
 * @author Antoine Hedgcock
 */

namespace CrateIntegrationTest\PDO;

use Crate\PDO\PDO;
use PHPUnit_Framework_TestCase;

abstract class AbstractIntegrationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PDO
     */
    protected $pdo;

    protected function setUp()
    {
        $this->pdo = new PDO('http://localhost:4200/_sql', null, null, []);
        $this->pdo->query('CREATE TABLE test_table (id INTEGER PRIMARY KEY, name string) clustered into 1 shards with (number_of_replicas = 0)');
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
}
