<?php
/**
 * @author Antoine Hedgcock
 */

namespace CrateIntegrationTest\PDO;

use Crate\PDO\PDO;

class PDOStatementTest extends AbstractIntegrationTest
{
    public function testFetchColumn()
    {
        $this->insertRows(5);

        $statement = $this->pdo->prepare('SELECT * FROM test_table');

        $result = [];

        while ($columnValue = $statement->fetchColumn()) {
            $result[] = $columnValue;
        }

        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testFetchBound()
    {
        $expected = [
            ['id' => 1, 'name' => 'first'],
            ['id' => 2, 'name' => 'second'],
            ['id' => 3, 'name' => 'third'],
        ];

        foreach ($expected as $row) {
            $this->insertRow($row['id'], $row['name']);
        }

        $id    = null;
        $name  = null;
        $index = 0;

        $statement = $this->pdo->prepare('SELECT * from test_table');
        $statement->bindColumn('id', $id);
        $statement->bindColumn('name', $name);

        while ($row = $statement->fetch(PDO::FETCH_BOUND)) {

            $this->assertEquals($expected[$index]['id'], $id);
            $this->assertEquals($expected[$index]['name'], $name);

            $index++;
        }
    }
}
