<?php
// @todo license headers to be added

namespace CrateTest\PDO;

use Crate\PDO\PDO;
use Crate\PDO\PDOInterface;
use Crate\PDO\PDOStatement;
use Crate\Stdlib\Collection;
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
}
