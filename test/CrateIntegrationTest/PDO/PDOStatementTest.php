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

        $statement = $this->pdo->prepare('SELECT * FROM test_table');
        $statement->bindColumn('id', $id);
        $statement->bindColumn('name', $name);

        while ($row = $statement->fetch(PDO::FETCH_BOUND)) {

            $this->assertEquals($expected[$index]['id'], $id);
            $this->assertEquals($expected[$index]['name'], $name);

            $index++;
        }

        $this->assertEquals(3, $index);
    }

    public function testFetchAllWithNumStyle()
    {
        $expected = [
            [1, 'first'],
            [2, 'second'],
            [3, 'third'],
        ];

        foreach ($expected as list($id, $name)) {
            $this->insertRow($id, $name);
        }

        $statement = $this->pdo->prepare('SELECT * FROM test_table');
        $statement->execute();

        $this->assertEquals($expected, $statement->fetchAll(PDO::FETCH_NUM));
    }

    public function testFetchAllWithAssocStyle()
    {
        $expected = [
            ['id' => 1, 'name' => 'first'],
            ['id' => 2, 'name' => 'second'],
            ['id' => 3, 'name' => 'third'],
        ];

        foreach ($expected as $row) {
            $this->insertRow($row['id'], $row['name']);
        }

        $statement = $this->pdo->prepare('SELECT * FROM test_table');
        $statement->execute();

        $this->assertEquals($expected, $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function testFetchAllWithBothStyle()
    {
        $expected = [
            [0 => 1, 'id' => 1, 1 => 'first', 'name' => 'first'],
            [0 => 2, 'id' => 2, 1 => 'second', 'name' => 'second'],
            [0 => 3, 'id' => 3, 1 => 'third', 'name' => 'third'],
        ];

        foreach ($expected as $row) {
            $this->insertRow($row['id'], $row['name']);
        }

        $statement = $this->pdo->prepare('SELECT * FROM test_table');
        $statement->execute();

        // In theory this should be assertSame, but implementing that would be incredibly slow
        $this->assertEquals($expected, $statement->fetchAll(PDO::FETCH_BOTH));
    }

    public function testFetchAllWithFuncStyle()
    {
        $expected = [
            ['id' => 1, 'name' => 'first'],
            ['id' => 2, 'name' => 'second'],
            ['id' => 3, 'name' => 'third'],
        ];

        foreach ($expected as $row) {
            $this->insertRow($row['id'], $row['name']);
        }

        $statement = $this->pdo->prepare('SELECT * FROM test_table');
        $statement->execute();

        $index    = 0;
        $callback = function ($id, $name) {
            return sprintf('%d:%s', $id, $name);
        };

        $resultSet = $statement->fetchAll(PDO::FETCH_FUNC, $callback);

        foreach ($resultSet as $result) {
            $this->assertEquals(sprintf('%d:%s', $expected[$index]['id'], $expected[$index]['name']), $result);
            $index++;
        }

        $this->assertEquals(count($expected), $index);
    }
}
