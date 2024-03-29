<?php
/**
 * Licensed to CRATE Technology GmbH("Crate") under one or more contributor
 * license agreements.  See the NOTICE file distributed with this work for
 * additional information regarding copyright ownership.  Crate licenses
 * this file to you under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.  You may
 * obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.  See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * However, if you have executed another commercial license agreement
 * with Crate these terms will supersede the license and you may use the
 * software solely pursuant to the terms of the relevant commercial agreement.
 */

declare(strict_types=1);

namespace Crate\PDO;

use ArrayIterator;
use Closure;
use Crate\Stdlib\ArrayUtils;
use Crate\Stdlib\CollectionInterface;
use Crate\Stdlib\CrateConst;
use IteratorAggregate;
use PDOStatement as BasePDOStatement;

class PDOStatement extends BasePDOStatement implements IteratorAggregate
{
    use PDOStatementImplementation;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string|null
     */
    private $errorCode;

    /**
     * @var string|null
     */
    private $errorMessage;

    /**
     * @var string
     */
    private $sql;

    /**
     * @var array
     */
    private $options = [
        'bulkMode'           => false,
        'fetchMode'          => null,
        'fetchColumn'        => 0,
        'fetchClass'         => 'array',
        'fetchClassCtorArgs' => null,
    ];

    /**
     * Used for the {@see PDO::FETCH_BOUND}
     *
     * @var array
     */
    private $columnBinding = [];

    /**
     * @var CollectionInterface|null
     */
    private $collection;

    /**
     * @var PDOInterface
     */
    private $pdo;

    /**
     * @var Closure
     */
    private $request;

    private $namedToPositionalMap = [];

    /**
     * @param PDOInterface $pdo
     * @param Closure      $request
     * @param string       $sql
     * @param array        $options
     */
    public function __construct(PDOInterface $pdo, Closure $request, $sql, array $options)
    {
        $this->sql     = $this->replaceNamedParametersWithPositionals($sql);
        $this->pdo     = $pdo;
        $this->options = array_merge($this->options, $options);
        $this->request = $request;
    }

    private function replaceNamedParametersWithPositionals($sql)
    {
        if (strpos($sql, ':') === false) {
            return $sql;
        }
        $pattern = '/:((?:[\w|\d|_](?=([^\'\\\]*(\\\.|\'([^\'\\\]*\\\.)*[^\'\\\]*\'))*[^\']*$))*)/';

        $idx      = 1;
        $callback = function ($matches) use (&$idx) {
            $value = $matches[1];
            if (empty($value)) {
                return $matches[0];
            }
            $this->namedToPositionalMap[$idx] = $value;
            $idx++;

            return '?';
        };

        return preg_replace_callback($pattern, $callback, $sql);
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
        return $this->options['fetchMode'] ?: $this->pdo->getAttribute(PDOCrateDB::ATTR_DEFAULT_FETCH_MODE);
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

        if (!$this->isSuccessful()) {
            return;
        }

        foreach ($this->columnBinding as $column => &$metadata) {
            $index = $this->collection->getColumnIndex($column);
            if ($index === null) {
                // todo: I would like to throw an exception and tell someone they screwed up
                // but i think that would violate the PDO api
                continue;
            }

            // Update by reference
            $value           = $this->typedValue($row[$index], $metadata['type']);
            $metadata['ref'] = $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function execute($input_parameters = null): bool
    {
        $params = ArrayUtils::toArray($input_parameters);

        // In bulk mode, propagate input parameters 1:1.
        // In regular mode, translate input parameters to `bindValue` calls.
        if ($this->options["bulkMode"] !== true) {
            $params = $this->bindValues($params);
        }

        $result = $this->request->__invoke($this, $this->sql, $params);

        if (is_array($result)) {
            $this->errorCode    = strval($result['code']);
            $this->errorMessage = strval($result['message']);

            return false;
        }

        $this->collection = $result;

        return true;
    }

    /**
     * Bind `execute`'s $input_parameters values to statement handle.
     */
    private function bindValues(array $params_in): array
    {
        $zero_based = array_key_exists(0, $params_in);
        foreach ($params_in as $parameter => $value) {
            if (is_int($parameter) && $zero_based) {
                $parameter++;
            }
            $this->bindValue($parameter, $value);
        }

        // parameter binding might be unordered, so sort it before execute
        ksort($this->parameters);
        return array_values($this->parameters);
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function fetch($fetch_style = null, $cursor_orientation = PDOCrateDB::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        if (!$this->hasExecuted()) {
            $this->execute();
        }

        if (!$this->isSuccessful()) {
            return false;
        }

        if ($this->collection === null || !$this->collection->valid()) {
            return false;
        }

        // Get the current row
        $row = $this->collection->current();

        // Traverse
        $this->collection->next();

        $fetch_style = $fetch_style ?: $this->getFetchStyle();

        switch ($fetch_style) {
            case PDOCrateDB::FETCH_NAMED:
            case PDOCrateDB::FETCH_ASSOC:
                return array_combine($this->collection->getColumns(false), $row);

            case PDOCrateDB::FETCH_BOTH:
                return array_merge($row, array_combine($this->collection->getColumns(false), $row));

            case PDOCrateDB::FETCH_BOUND:
                $this->updateBoundColumns($row);

                return true;

            case PDOCrateDB::FETCH_NUM:
                return $row;

            case PDOCrateDB::FETCH_OBJ:
                return $this->getObjectResult($this->collection->getColumns(false), $row);

            default:
                throw new Exception\UnsupportedException('Unsupported fetch style');
        }
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function bindParam(
        $parameter,
        &$variable,
        $data_type = PDOCrateDB::PARAM_STR,
        $length = null,
        $driver_options = null
    ) {
        if (is_numeric($parameter)) {
            if ($parameter == 0) {
                throw new Exception\UnsupportedException("0-based parameter binding not supported, use 1-based");
            }
            $this->parameters[$parameter - 1] = &$variable;
        } else {
            $namedParameterKey = substr($parameter, 0, 1) === ':' ? substr($parameter, 1) : $parameter;
            if (in_array($namedParameterKey, $this->namedToPositionalMap, true)) {
                foreach ($this->namedToPositionalMap as $key => $value) {
                    if ($value == $namedParameterKey) {
                        $this->parameters[$key] = &$variable;
                    }
                }
            } else {
                throw new Exception\OutOfBoundsException(
                    sprintf('The named parameter "%s" does not exist', $parameter)
                );
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null)
    {
        $type = $type ?: PDOCrateDB::PARAM_STR;

        $this->columnBinding[$column] = [
            'ref'        => &$param,
            'type'       => $type,
            'maxlen'     => $maxlen,
            'driverdata' => $driverdata,
        ];
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function bindValue($parameter, $value, $data_type = PDOCrateDB::PARAM_STR)
    {
        $value = $this->typedValue($value, $data_type);
        $this->bindParam($parameter, $value, $data_type);
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function rowCount(): int
    {
        if (!$this->hasExecuted()) {
            $this->execute();
        }

        if (!$this->isSuccessful()) {
            return 0;
        }

        return $this->collection->count();
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function fetchColumn($column_number = 0)
    {
        if (!is_int($column_number)) {
            throw new Exception\InvalidArgumentException('column_number must be a valid integer');
        }

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

        if ($column_number >= count($row)) {
            throw new Exception\OutOfBoundsException(
                sprintf('The column "%d" with the zero-based does not exist', $column_number)
            );
        }

        return $row[$column_number];
    }

    /**
     * {@inheritDoc}
     */
    public function doFetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = null)
    {
        if (!$this->hasExecuted()) {
            $this->execute();
        }

        if (!$this->isSuccessful()) {
            return false;
        }

        $fetch_style = $fetch_style ?: $this->getFetchStyle();

        switch ($fetch_style) {
            case PDOCrateDB::FETCH_NUM:
                return $this->collection->getRows();

            case PDOCrateDB::FETCH_NAMED:
            case PDOCrateDB::FETCH_ASSOC:
                $columns = $this->collection->getColumns(false);

                return $this->collection->map(function (array $row) use ($columns) {
                    return array_combine($columns, $row);
                });

            case PDOCrateDB::FETCH_BOTH:
                $columns = $this->collection->getColumns(false);

                return $this->collection->map(function (array $row) use ($columns) {
                    return array_merge($row, array_combine($columns, $row));
                });

            case PDOCrateDB::FETCH_FUNC:
                if (!is_callable($fetch_argument)) {
                    throw new Exception\InvalidArgumentException('Second argument must be callable');
                }

                return $this->collection->map(function (array $row) use ($fetch_argument) {
                    return call_user_func_array($fetch_argument, $row);
                });

            case PDOCrateDB::FETCH_COLUMN:
                $columnIndex = $fetch_argument ?: $this->options['fetchColumn'];

                if (!is_int($columnIndex)) {
                    throw new Exception\InvalidArgumentException('Second argument must be a integer');
                }

                $columns = $this->collection->getColumns(false);
                if (!isset($columns[$columnIndex])) {
                    throw new Exception\OutOfBoundsException(
                        sprintf('Column with the index %d does not exist.', $columnIndex)
                    );
                }

                return $this->collection->map(function (array $row) use ($columnIndex) {
                    return $row[$columnIndex];
                });
            case PDOCrateDB::FETCH_OBJ:
                $columns = $this->collection->getColumns(false);

                return $this->collection->map(function (array $row) use ($columns) {
                    return $this->getObjectResult($columns, $row);
                });

            default:
                throw new Exception\UnsupportedException('Unsupported fetch style');
        }
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function fetchObject($class_name = null, $ctor_args = null)
    {
        throw new Exception\UnsupportedException;
    }

    /**
     * {@inheritDoc}
     */
    public function errorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function errorInfo()
    {
        if ($this->errorCode === null) {
            return ["00000", null, null];
        }

        switch ($this->errorCode) {
            case CrateConst::ERR_INVALID_SQL:
                $ansiErrorCode = '42000';
                break;

            default:
                $ansiErrorCode = 'Not available';
                break;
        }

        return [
            strval($ansiErrorCode),
            intval($this->errorCode),
            strval($this->errorMessage),
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @param int $attribute
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function setAttribute($attribute, $value)
    {
        throw new Exception\UnsupportedException('This driver doesn\'t support setting attributes');
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function getAttribute($attribute)
    {
        throw new Exception\UnsupportedException('This driver doesn\'t support getting attributes');
    }

    /**
     * {@inheritDoc}
     */
    public function columnCount(): int
    {
        if (!$this->hasExecuted()) {
            $this->execute();
        }

        return count($this->collection->getColumns(false));
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function getColumnMeta($column)
    {
        throw new Exception\UnsupportedException;
    }

    /**
     * {@inheritDoc}
     */
    public function doSetFetchMode($mode, $params = null)
    {
        $args     = func_get_args();
        $argCount = count($args);

        switch ($mode) {
            case PDOCrateDB::FETCH_COLUMN:
                if ($argCount != 2) {
                    throw new Exception\InvalidArgumentException('fetch mode requires the colno argument');
                }

                if (!is_int($params)) {
                    throw new Exception\InvalidArgumentException('colno must be an integer');
                }

                $this->options['fetchMode']   = $mode;
                $this->options['fetchColumn'] = $params;
                break;

            case PDOCrateDB::FETCH_ASSOC:
            case PDOCrateDB::FETCH_NUM:
            case PDOCrateDB::FETCH_BOTH:
            case PDOCrateDB::FETCH_BOUND:
            case PDOCrateDB::FETCH_NAMED:
            case PDOCrateDB::FETCH_OBJ:
                if ($params !== null) {
                    throw new Exception\InvalidArgumentException('fetch mode doesn\'t allow any extra arguments');
                }

                $this->options['fetchMode'] = $mode;
                break;

            default:
                throw new Exception\UnsupportedException('Invalid fetch mode specified');
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function nextRowset(): bool
    {
        if (!$this->hasExecuted()) {
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
    public function closeCursor(): bool
    {
        $this->errorCode  = null;
        $this->collection = null;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function debugDumpParams(): ?bool
    {
        throw new Exception\UnsupportedException('Not supported, use var_dump($stmt) instead');
    }

    /**
     * {@Inheritdoc}
     */
    public function getIterator(): \Iterator
    {
        $results = $this->fetchAll();
        if ($results === false) {
            throw new Exception\RuntimeException('Failure when fetching data');
        }
        return new ArrayIterator($results);
    }

    private function typedValue($value, $data_type)
    {
        if (null === $value) {
            // Do not typecast null values
            return null;
        }

        switch ($data_type) {
            case PDOCrateDB::PARAM_FLOAT:
            case PDOCrateDB::PARAM_DOUBLE:
                return (float)$value;

            case PDOCrateDB::PARAM_INT:
            case PDOCrateDB::PARAM_LONG:
                return (int)$value;

            case PDOCrateDB::PARAM_NULL:
                return null;

            case PDOCrateDB::PARAM_BOOL:
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);

            case PDOCrateDB::PARAM_STR:
            case PDOCrateDB::PARAM_IP:
                return (string)$value;

            case PDOCrateDB::PARAM_OBJECT:
            case PDOCrateDB::PARAM_ARRAY:
                return (array)$value;

            case PDOCrateDB::PARAM_TIMESTAMP:
                if (is_numeric($value)) {
                    return (int)$value;
                }

                return (string)$value;

            default:
                throw new Exception\InvalidArgumentException(sprintf('Parameter type %s not supported', $data_type));
        }
    }

    /**
     * Generate object from array
     *
     * @param array $columns
     * @param array $row
     */
    private function getObjectResult(array $columns, array $row)
    {
        $obj = new \stdClass();
        foreach ($columns as $key => $column) {
            $obj->{$column} = $row[$key];
        }

        return $obj;
    }

    public function isBulkMode()
    {
        return $this->options["bulkMode"];
    }
}
