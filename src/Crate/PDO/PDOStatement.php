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

    /**
     * @param PDOInterface $pdo
     * @param Closure      $request
     * @param string       $sql
     * @param array        $options
     */
    public function __construct(PDOInterface $pdo, Closure $request, $sql, array $options)
    {
        $this->sql     = $sql;
        $this->pdo     = $pdo;
        $this->options = array_merge($this->options, $options);
        $this->request = $request;
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

        $result = $this->request->__invoke($this, $this->sql, $this->parameters);

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
                return array_combine($this->collection->getColumns(false), $row);

            case PDO::FETCH_BOTH:
                return array_merge($row, array_combine($this->collection->getColumns(false), $row));

            case PDO::FETCH_BOUND:
                $this->updateBoundColumns($row);
                return true;

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
        $this->parameters[$parameter] = &$variable;
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

        return count($this->collection);
    }

    /**
     * {@inheritDoc}
     */
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

        if (!isset($row[$column_number])) {
            throw new Exception\OutOfBoundsException(
                sprintf('The column "%d" with the zero-based does not exist', $column_number)
            );
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
                    throw new Exception\InvalidArgumentException('Second argument must be callable');
                }

                return $this->collection->map(function (array $row) use ($fetch_argument) {
                    return call_user_func_array($fetch_argument, $row);
                });

            case PDO::FETCH_COLUMN:
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
        throw new Exception\UnsupportedException('This driver doesn\'t support setting attributes');
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute($attribute)
    {
        throw new Exception\UnsupportedException('This driver doesn\'t support getting attributes');
    }

    /**
     * {@inheritDoc}
     */
    public function columnCount()
    {
        if (!$this->hasExecuted()) {
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
                    throw new Exception\InvalidArgumentException('fetch mode requires the colno argument');
                }

                if (!is_int($params)) {
                    throw new Exception\InvalidArgumentException('colno must be an integer');
                }

                $this->options['fetchMode']   = $mode;
                $this->options['fetchColumn'] = $params;
                break;

            case PDO::FETCH_ASSOC:
            case PDO::FETCH_NUM:
            case PDO::FETCH_BOTH:
            case PDO::FETCH_BOUND:
            case PDO::FETCH_NAMED:
                if ($params !== null) {
                    throw new Exception\InvalidArgumentException('fetch mode doesn\'t allow any extra arguments');
                }

                $this->options['fetchMode'] = $mode;
                break;

            default:
                throw new Exception\UnsupportedException('Invalid fetch mode specified');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function nextRowset()
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
    public function closeCursor()
    {
        throw new Exception\UnsupportedException;
    }

    /**
     * {@inheritDoc}
     */
    public function debugDumpParams()
    {
        throw new Exception\UnsupportedException('Not supported, use var_dump($stmt) instead');
    }

    /**
     * {@Inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->fetchAll());
    }
}
