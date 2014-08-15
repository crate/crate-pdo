<?php
// @todo license headers to be added

namespace Crate\PDO;

use Crate\Stdlib\ArrayUtils;
use Crate\Stdlib\Collection;
use Crate\Stdlib\CrateConst;
use IteratorAggregate;
use PDOStatement as BasePDOStatement;

class PDOStatement extends BasePDOStatement implements IteratorAggregate
{
    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string|null
     */
    private $errorCode = null;

    /**
     * @var string|null
     */
    private $errorMessage = null;

    /**
     * @var string
     */
    private $sql;

    /**
     * @var array
     */
    private $options = [
        'fetchMode'          => null,
        'fetchColumn'        => 0,
        'fetchClass'         => 'stdClass',
        'fetchClassCtorArgs' => null,
    ];

    /**
     * Used for the {@see PDO::FETCH_BOUND}
     *
     * @var array
     */
    private $columnBinding = [];

    /**
     * @var Collection|null
     */
    private $collection = null;

    /**
     * @var PDOInterface
     */
    private $pdo;

    /**
     * @param PDOInterface    $pdo
     * @param string          $sql
     * @param array           $options
     */
    public function __construct(PDOInterface $pdo, $sql, array $options)
    {
        $this->sql     = $sql;
        $this->pdo     = $pdo;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Determines if the statement has been executed
     *
     * @internal
     *
     * @return bool
     */
    private function hasExecuted()
    {
        return ($this->collection !== null || $this->errorCode !== null);
    }

    /**
     * Internal pointer to mark the state of the current query
     *
     * @internal
     *
     * @return bool
     */
    private function isSuccessful()
    {
        if (!$this->hasExecuted()) {
            // @codeCoverageIgnoreStart
            throw new Exception\LogicException('The statement has not been executed yet');
            // @codeCoverageIgnoreEnd
        }

        return $this->collection !== null;
    }

    /**
     * Get the fetch style to be used
     *
     * @internal
     *
     * @return int
     */
    private function getFetchStyle()
    {
        return $this->options['fetchMode'] ?: $this->pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);
    }

    /**
     * Update all the bound column references
     *
     * @internal
     *
     * @param array $row
     *
     * @return void
     */
    private function updateBoundColumns(array $row)
    {
        foreach ($this->columnBinding as $column => &$metadata) {

            $index = $this->collection->getColumnIndex($column);
            if ($index === null) {
                // todo: I would like to throw an exception and tell someone they screwed up
                // but i think that would violate the PDO api
                continue;
            }

            $value = $row[$index];

            switch ($metadata['type'])
            {
                case PDO::PARAM_INT:
                    $value = (int) $value;
                    break;

                case PDO::PARAM_NULL:
                    $value = null;
                    break;

                case PDO::PARAM_BOOL:
                    $value = (bool) $value;
                    break;

                case PDO::PARAM_STR:
                    $value = (string) $value;
                    break;

                case PDO::PARAM_LOB:
                    // todo: What do i do here ?
                    break;
            }

            // Update by reference
            $metadata['ref'] = $value;
        }

    }

    /**
     * {@inheritDoc}
     */
    public function execute($input_parameters = null)
    {
        foreach (ArrayUtils::toArray($input_parameters) as $parameter => $value) {
            $this->bindValue($parameter, $value);
        }

        $result = $this->pdo->doRequest($this, $this->sql, $this->parameters);

        if (is_array($result)) {
            $this->errorCode    = $result['code'];
            $this->errorMessage = $result['message'];

            return false;
        }

        $this->collection = $result;
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        if (!$this->hasExecuted()) {
            $this->execute();
        }

        if (!$this->isSuccessful()) {
            return false;
        }

        if (!$this->collection->valid()) {
            return false;
        }

        // Get the current row
        $row = $this->collection->current();

        // Traverse
        $this->collection->next();

        $fetch_style = $fetch_style ?: $this->getFetchStyle();

        switch ($fetch_style)
        {
            case PDO::FETCH_NAMED:
            case PDO::FETCH_ASSOC:
                return array_combine($this->collection->getColumns(), $row);

            case PDO::FETCH_BOTH:
                return array_merge($row, array_values($row));

            case PDO::FETCH_BOUND:
                $this->updateBoundColumns($row);
                return true;

            case PDO::FETCH_CLASS:
                break;

            case PDO::FETCH_INTO:
                break;

            case PDO::FETCH_LAZY:
                break;

            case PDO::FETCH_NUM:
                return $row;

            default:
                throw new Exception\UnsupportedException('Unsupported fetch style');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function bindParam(
        $parameter,
        & $variable,
        $data_type = PDO::PARAM_STR,
        $length = null,
        $driver_options = null
    ) {
        $this->parameters[$parameter] = &$parameter;
    }

    /**
     * {@inheritDoc}
     */
    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null)
    {
        $type = $type ?: PDO::PARAM_STR;

        $this->columnBinding[$column] = [
            'ref'        => &$param,
            'type'       => $type,
            'maxlen'     => $maxlen,
            'driverdata' => $driverdata
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        switch ($data_type)
        {
            case PDO::PARAM_INT:
                $value = (int) $value;
                break;

            case PDO::PARAM_NULL:
                $value = null;
                break;

            case PDO::PARAM_BOOL:
                $value = (bool) $value;
                break;

            case PDO::PARAM_STR:
                $value = (string) $value;
                break;

            case PDO::PARAM_LOB:
                // todo: What do i do here ?
                throw new \Exception('Not yet implemented');
        }

        $this->parameters[$parameter] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function rowCount()
    {
        if (!$this->hasExecuted()) {
            $this->execute();
        }

        if (!$this->isSuccessful()) {
            return false;
        }

        return $this->collection->count();
    }

    /**
     * {@inheritDoc}
     */
    public function fetchColumn($column_number = 0)
    {
        if (!$this->hasExecuted()) {
            $this->execute();
        }

        if (!$this->isSuccessful()) {
            return false;
        }

        if (!$this->collection->valid()) {
            return false;
        }

        $row = $this->collection->current();
        $this->collection->next();


        if (!isset($row[$column_number])) {
            // todo: Not sure how what actually happens here
        }

        return $row[$column_number];
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = [])
    {
        if (!$this->hasExecuted()) {
            $this->execute();
        }

        if (!$this->isSuccessful()) {
            return false;
        }

        $fetch_style = $fetch_style ?: $this->getFetchStyle();

        switch ($fetch_style)
        {
            case PDO::FETCH_NUM:
                return $this->collection->getRows();

            case PDO::FETCH_NAMED:
            case PDO::FETCH_ASSOC:
                $columns = array_flip($this->collection->getColumns());

                return $this->collection->map(function (array $row) use ($columns) {
                    return array_combine($columns, $row);
                });

            case PDO::FETCH_BOTH:
                $columns = array_flip($this->collection->getColumns());

                return $this->collection->map(function (array $row) use ($columns) {
                    return array_merge($row, array_combine($columns, $row));
                });

            case PDO::FETCH_FUNC:
                if (!is_callable($fetch_argument)) {
                    throw new Exception\PDOException('Second argument must be callable', 'HY000');
                }

                return $this->collection->map(function (array $row) use ($fetch_argument) {
                    return call_user_func_array($fetch_argument, $row);
                });

            case PDO::FETCH_COLUMN:
                if (!is_int($fetch_argument)) {
                    throw new Exception\PDOException('Second argument must be a integer', 'HY000');
                }

                $columns = array_flip($this->collection->getColumns());

                if (!isset($columns[$fetch_argument])) {
                    throw new Exception\OutOfBoundsException(
                        sprintf('Column with the index %d does not exist.', $fetch_argument)
                    );
                }

                return $this->collection->map(function(array $row) use ($fetch_argument) {
                    return $row[$fetch_argument];
                });

            case PDO::FETCH_CLASS:
                break;

            case PDO::FETCH_INTO:
                break;

            case PDO::FETCH_LAZY:
                break;

            default:
                throw new Exception\UnsupportedException('Unsupported fetch style');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fetchObject($class_name = null, $ctor_args = null)
    {
        throw new Exception\UnsupportedException;
    }

    /**
     * {@inheritDoc}
     */
    public function errorCode()
    {
        return $this->errorCode;
    }

    /**
     * {@inheritDoc}
     */
    public function errorInfo()
    {
        if ($this->errorCode === null) {
            return null;
        }

        switch ($this->errorCode)
        {
            case CrateConst::ERR_INVALID_SQL:
                $ansiErrorCode = 42000;
                break;

            default:
                $ansiErrorCode = 'Not available';
                break;
        }

        return [
            $ansiErrorCode,
            $this->errorCode,
            $this->errorMessage
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setAttribute($attribute, $value)
    {
        throw new Exception\PDOException('This driver doesn\'t support setting attributes');
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute($attribute)
    {
        throw new Exception\PDOException('This driver doesn\'t support getting attributes');
    }

    /**
     * {@inheritDoc}
     */
    public function columnCount()
    {
        if ($this->hasExecuted()) {
            $this->execute();
        }

        return count($this->collection->getColumns());
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnMeta($column)
    {
        throw new Exception\UnsupportedException;
    }

    /**
     * {@inheritDoc}
     */
    public function setFetchMode($mode, $params = null)
    {
        $args     = func_get_args();
        $argCount = count($args);

        switch ($mode)
        {
            case PDO::FETCH_COLUMN:
                if ($argCount != 2) {
                    throw new Exception\PDOException('fetch mode requires the colno argument', 'HY000');
                }

                if (!is_int($params)) {
                    throw new Exception\PDOException('colno must be an integer', 'HY000');
                }

                $this->options['fetchMode']   = $mode;
                $this->options['fetchColumn'] = $params;
                break;

            case PDO::FETCH_CLASSTYPE:
                if ($argCount != 1) {
                    throw new Exception\PDOException('fetch mode doesn\'t allow any extra arguments', 'HY000');
                }

                $this->options['fetchMode'] = $mode;
                break;

            case PDO::FETCH_CLASS:
                if ($argCount < 2) {
                    throw new Exception\PDOException('fetch mode requires the classname argument', 'HY000');
                }

                if (!is_string($params)) {
                    throw new Exception\PDOException('classname must be a string', 'HY000');
                }

                $ctorArgs = ($argCount == 3) ? $args[2] : null;
                if ($ctorArgs !== null && !is_array($ctorArgs)) {
                    throw new Exception\PDOException('ctor_args must be either NULL or an array', 'HY000');
                }

                if (!class_exists($params, true)) {
                    throw new Exception\PDOException(
                        sprintf('A class by the name of %s could not be found', $params),
                        'HY000'
                    );
                }

                $this->options['fetchMode']          = $mode;
                $this->options['fetchClass']         = $params;
                $this->options['fetchClassCtorArgs'] = $ctorArgs;
                break;

            case PDO::FETCH_INTO:
                if ($argCount < 2) {
                    throw new Exception\PDOException('fetch mode requires the object parameter', 'HY000');
                }

                if (!is_object($params)) {
                    throw new Exception\PDOException('object must be an object', 'HY000');
                }

                $this->options['fetchMode'] = $mode;
                $this->options['fetchInto'] = $params;
                break;

            case PDO::FETCH_ASSOC:
            case PDO::FETCH_NUM:
            case PDO::FETCH_BOTH:
            case PDO::FETCH_BOUND:
            case PDO::FETCH_NAMED:
            case PDO::FETCH_KEY_PAIR:
            case PDO::FETCH_LAZY:
                if ($params !== null) {
                    throw new Exception\PDOException('fetch mode doesn\'t allow any extra arguments', 'HY000');
                }

                $this->options['fetchMode'] = $mode;
                break;

            default:
                throw new Exception\UnsupportedException('Invalid fetch mode specified', 'HY22003');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function nextRowset()
    {
        if ($this->hasExecuted()) {
            $this->execute();
        }

        if (!$this->isSuccessful()) {
            return false;
        }

        $this->collection->next();
        return $this->collection->valid();
    }

    /**
     * {@inheritDoc}
     */
    public function closeCursor()
    {
        throw new Exception\UnsupportedException;
    }

    /**
     * {@inheritDoc}
     */
    public function debugDumpParams()
    {
    }

    /**
     * {@Inheritdoc}
     */
    public function getIterator()
    {
        if ($this->hasExecuted()) {
            $this->execute();
        }

        return $this->collection;
    }
}
