<?php
/**
 * @author Antoine Hedgcock
 */

namespace Crate\PDO\Exception;

use Exception;

class UnsupportedException extends PDOException
{
    public function __construct($message = 'Unsupported functionality', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
