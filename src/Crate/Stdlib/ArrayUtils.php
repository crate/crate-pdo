<?php
/**
 * @author Antoine Hedgcock
 */

namespace Crate\Stdlib;

use Traversable;

class ArrayUtils
{
    public static function toArray($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value === null) {
            return [];
        }

        if (!$value instanceof Traversable) {
            throw new Exception\InvalidArgumentException();
        }

        return iterator_to_array($value);
    }
}
