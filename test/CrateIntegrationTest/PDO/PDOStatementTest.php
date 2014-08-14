<?php
/**
 * @author Antoine Hedgcock
 */

namespace CrateIntegrationTest\PDO;

class PDOStatementTest extends AbstractIntegrationTest
{
    public function testFetchColumn()
    {
        $this->insertRow(5);

        $statement = $this->pdo->prepare('SELECT * FROM test_table');

        $result = [];

        while ($columnValue = $statement->fetchColumn()) {
            $result[] = $columnValue;
        }

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }
}
