<?php
// @todo license headers to be added

namespace CrateTest\PDO;

use Crate\PDO\PDO;
use Crate\PDO\PDOInterface;
use Crate\PDO\PDOStatement;
use Crate\Stdlib\Collection;
use Crate\Stdlib\CollectionInterface;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Tests for {@see \Crate\PDO\PDOStatement}
 *
 * @coversDefaultClass \Crate\PDO\PDOStatement
 * @covers ::<!public>
 *
 * @group unit
 * @group statement
 */
class PDOStatementTest extends PHPUnit_Framework_TestCase
{
    const SQL = 'SELECT * FROM table_name';

    /**
     * @var PDO|PHPUnit_Framework_MockObject_MockObject
     */
    protected $pdo;

    /**
     * @var PDOStatement
     */
    protected $statement;

    protected function setUp()
    {
        $this->pdo = $this->getMock(PDOInterface::class);

        $this->statement = new PDOStatement($this->pdo, static::SQL, []);
    }

    /**
     * @return CollectionInterface
     */
    private function getPopulatedCollection()
    {
        $data = [
            [1, 'foo', false],
            [2, 'bar', true],
        ];

        $columns = ['id', 'name', 'active'];

        return new Collection($data, $columns, 0, count($data));
    }

    /**
     * @covers ::__construct
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Crate\PDO\PDOStatement', $this->statement);
        $this->assertInstanceOf('PDOStatement', $this->statement);
    }

    /**
     * @covers ::closeCursor
     */
    public function testCloseCursorThrowsUnsupportedException()
    {
        $this->setExpectedException('Crate\PDO\Exception\UnsupportedException');
        $this->statement->closeCursor();
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithErrorResponse()
    {
        $this->pdo
            ->expects($this->once())
            ->method('doRequest')
            ->with($this->statement, static::SQL, [])
            ->will($this->returnValue(['code' => 1337, 'message' => 'failed']));

        $this->assertFalse($this->statement->execute());

        list ($ansiErrorCode, $driverCode, $message) = $this->statement->errorInfo();

        $this->assertEquals(1337, $driverCode);
        $this->assertEquals('failed', $message);
    }

    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $collection = new Collection([], [], 0, 0);
        $parameters = ['foo' => 'bar'];

        $this->pdo
            ->expects($this->once())
            ->method('doRequest')
            ->with($this->statement, static::SQL, $parameters)
            ->will($this->returnValue($collection));

        $this->assertTrue($this->statement->execute($parameters));
    }

    /**
     * @covers ::bindParam
     */
    public function testBindParam()
    {
        $initial  = 'foo';
        $expected = 'bar';

        $this->pdo
            ->expects($this->once())
            ->method('doRequest')
            ->with($this->statement, static::SQL, ['var' => $expected]);

        $this->statement->bindParam('var', $initial);

        // Update bar prior to calling execute
        $initial = $expected;

        $this->statement->execute();
    }

    /**
     * @covers ::bindColumn
     */
    public function testBindColumn()
    {
        $column     = 'column';
        $value      = 'value1';
        $type       = PDO::PARAM_STR;
        $maxlen     = 1000;
        $driverData = null;

        $this->statement->bindColumn($column, $value, $type, $maxlen, $driverData);

        $reflection = new ReflectionClass('Crate\PDO\PDOStatement');

        $property = $reflection->getProperty('columnBinding');
        $property->setAccessible(true);

        $columnBinding = $property->getValue($this->statement);

        $this->assertArrayHasKey($column, $columnBinding);
        $this->assertEquals($value, $columnBinding[$column]['ref']);

        $value = 'value2';

        $this->assertEquals($value, $columnBinding[$column]['ref']);
    }

    public function bindValueParameterProvider()
    {
        return [
            [PDO::PARAM_INT, '1', 1],
            [PDO::PARAM_NULL, '1', null],
            [PDO::PARAM_BOOL, '1', true],
            [PDO::PARAM_STR, '1', '1']
        ];
    }

    /**
     * @dataProvider bindValueParameterProvider
     * @covers ::bindValue
     *
     * @param int    $type
     * @param string $value
     * @param mixed  $expectedValue
     */
    public function testBindValue($type, $value, $expectedValue)
    {
        $this->statement->bindValue('column', $value, $type);

        $reflection = new ReflectionClass('Crate\PDO\PDOStatement');

        $property = $reflection->getProperty('parameters');
        $property->setAccessible(true);

        $castedValue = $property->getValue($this->statement);

        $this->assertSame($expectedValue, $castedValue['column']);
    }

    /**
     * @covers ::fetch
     */
    public function testFetchWithUnsuccessfulExecution()
    {
        $this->pdo
            ->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue(['code' => 1337, 'message' => 'expected failure']));

        $this->assertFalse($this->statement->fetch());
    }

    /**
     * @covers ::fetch
     */
    public function testFetchWithEmptyResult()
    {
        $collection = $this->getMock('Crate\Stdlib\CollectionInterface');
        $collection
            ->expects($this->once())
            ->method('valid')
            ->will($this->returnValue(false));

        $this->pdo
            ->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue($collection));

        $this->assertFalse($this->statement->fetch());
    }

    /**
     * @covers ::fetch
     */
    public function testFetchWithBoundStyle()
    {
        $id     = null;
        $name   = null;
        $active = null;

        $this->statement->bindColumn('id', $id, PDO::PARAM_INT);
        $this->statement->bindColumn('name', $name, PDO::PARAM_STR);
        $this->statement->bindColumn('active', $active, PDO::PARAM_BOOL);

        $this->assertNull($id);
        $this->assertNull($name);
        $this->assertNull($active);

        $this->pdo
            ->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue($this->getPopulatedCollection()));

        $this->statement->fetch(PDO::FETCH_BOUND);

        $this->assertSame(1, $id);
        $this->assertSame('foo', $name);
        $this->assertFalse($active);

        $this->statement->fetch(PDO::FETCH_BOUND);

        $this->assertSame(2, $id);
        $this->assertSame('bar', $name);
        $this->assertTrue($active);
    }

    public function fetchStyleProvider()
    {
        return [
            [PDO::FETCH_ASSOC, ['id' => 1, 'name' => 'foo', 'active' => false]],
            [PDO::FETCH_BOTH, [0 => 1, 1 => 'foo', 2 => false, 'id' => 1, 'name' => 'foo', 'active' => false]],
            [PDO::FETCH_NUM, [0 => 1, 1 => 'foo', 2 => false]]
        ];
    }

    /**
     * @dataProvider fetchStyleProvider
     * @covers ::fetch
     *
     * @param int   $fetchStyle
     * @param array $expected
     */
    public function testFetch($fetchStyle, array $expected)
    {
        $this->pdo
            ->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue($this->getPopulatedCollection()));

        $result = $this->statement->fetch($fetchStyle);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::fetch
     */
    public function testFetchWithUnsupportedFetchStyle()
    {
        $this->setExpectedException('Crate\PDO\Exception\UnsupportedException');

        $this->pdo
            ->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue($this->getPopulatedCollection()));

        $this->statement->fetch(PDO::FETCH_INTO);
    }

    /**
     * @covers ::rowCount
     */
    public function testRowCountWithFailedExecution()
    {
        $this->pdo
            ->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue(['code' => 1337, 'message' => 'expected failure']));

        $this->assertFalse($this->statement->rowCount());
    }

    /**
     * @covers ::rowCount
     */
    public function testRowCount()
    {
        $this->pdo
            ->expects($this->once())
            ->method('doRequest')
            ->will($this->returnValue($this->getPopulatedCollection()));

        $this->assertEquals(2, $this->statement->rowCount());
    }
}
