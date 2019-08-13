<?php
/**
 * @author Antoine Hedgcock
 */

namespace CrateTest\Stdlib;

use ArrayIterator;
use Crate\Stdlib\ArrayUtils;
use Crate\Stdlib\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrayUtilsTest
 *
 * @coversDefaultClass \Crate\Stdlib\ArrayUtils
 * @covers ::<!public>
 *
 * @group unit
 */
class ArrayUtilsTest extends TestCase
{
    /**
     * @covers ::toArray
     */
    public function testToArrayWithNull()
    {
        $this->assertEquals([], ArrayUtils::toArray(null));
    }

    /**
     * @covers ::toArray
     */
    public function testToArrayWithArray()
    {
        $this->assertEquals(['hello' => 'world'], ArrayUtils::toArray(['hello' => 'world']));
    }

    /**
     * @covers ::toArray
     */
    public function testToArrayWithIterator()
    {
        $this->assertEquals(['hello' => 'world'], ArrayUtils::toArray(new ArrayIterator(['hello' => 'world'])));
    }

    /**
     * @covers ::toArray
     */
    public function testToArrayWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);
        ArrayUtils::toArray('foo');
    }
}
