<?php

namespace CrateTest\Stdlib;

use Crate\Stdlib\BulkResponse;
use PHPUnit\Framework\TestCase;

/**
 * Class BulkResponseTest
 *
 * @coversDefaultClass \Crate\Stdlib\BulkResponse
 * @covers ::<!public>
 *
 * @group unit
 */
class BulkResponseTest extends TestCase
{
    /**
     * @var array
     */
    private $results = [
        ["rowcount" => 1],
        ["rowcount" => 1],
    ];

    /**
     * @var array
     */
    private $columns = ['id', 'name'];

    /**
     * @var BulkResponse
     */
    private $response;

    /**
     * @covers ::__construct
     */
    protected function setUp(): void
    {
        $this->response = new BulkResponse($this->results, $this->columns, 0);
    }

    /**
     * @covers ::map
     */
    public function testMap()
    {
        $result = $this->response->map(function(array $row) {
            return implode(':', $row);
        });

        $this->assertEquals([1, 1], $result);
    }

    /**
     * @covers ::getColumnIndex
     */
    public function testGetColumnIndex()
    {
        $this->assertNull($this->response->getColumnIndex('helloWorld'));

        $this->assertEquals(0, $this->response->getColumnIndex('id'));
        $this->assertEquals(1, $this->response->getColumnIndex('name'));
    }

    /**
     * @covers ::getColumns
     */
    public function testGetColumns()
    {
        $this->assertEquals(['id' => 0, 'name' => 1], $this->response->getColumns());
        $this->assertEquals(['id', 'name'], $this->response->getColumns(false));
    }

    /**
     * @covers ::getColumns
     */
    public function testGetColumnsSameColumnTwice() {
        $this->response = new BulkResponse([], ['id', 'id'], 0);
        $this->assertEquals(['id' => 0, 'id' => 1], $this->response->getColumns());
    }

    /**
     * @covers ::getRows
     */
    public function testGetRows()
    {
        $this->assertEquals($this->results, $this->response->getRows());
    }

    /**
     * @covers ::count
     */
    public function testCount()
    {
        $this->assertEquals(count($this->results), $this->response->count());
    }

    public function testIterator()
    {
        $this->assertInstanceOf('Iterator', $this->response);

        foreach ($this->response as $index => $row) {
            $this->assertEquals($this->results[$index], $row);
        }
    }
}
