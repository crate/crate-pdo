<?php
/**
 * @author Antoine Hedgcock
 */

namespace CrateTest\Stdlib;

use ArrayIterator;
use Crate\Stdlib\ArrayUtils;
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
     * @expectedException \Crate\Stdlib\Exception\InvalidArgumentException
     * @covers ::toArray
     */
    public function testToArrayWithInvalidValue()
    {
        ArrayUtils::toArray('foo');
    }
}
