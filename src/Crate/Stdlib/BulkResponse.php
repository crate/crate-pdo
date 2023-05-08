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

namespace Crate\Stdlib;

final class BulkResponse implements BulkResponseInterface
{
    /**
     * Result object for CrateDB bulk operations.
     * https://crate.io/docs/crate/reference/en/latest/interfaces/http.html#bulk-operations
     * @var array
     */
    private $results;

    /**
     * @var string[]
     */
    private $columnsAsKeys;

    /**
     * @var string[]
     */
    private $columnsAsValues;

    /**
     * @var int
     */
    private $duration;

    /**
     * @param array    $results
     * @param string[] $columns
     * @param float    $duration
     */
    public function __construct(array $results, array $columns, float $duration)
    {
        $this->results         = $results;
        $this->columnsAsKeys   = array_flip($columns);
        $this->columnsAsValues = $columns;
        $this->duration        = $duration;
    }

    /**
     * {@Inheritdoc}
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->results);
    }

    /**
     * {@Inheritdoc}
     */
    public function getColumnIndex($column)
    {
        if (isset($this->columnsAsKeys[$column])) {
            return $this->columnsAsKeys[$column];
        }

        return null;
    }

    /**
     * {@Inheritdoc}
     */
    public function getColumns($columnsAsKeys = true): array
    {
        return $columnsAsKeys ? $this->columnsAsKeys : $this->columnsAsValues;
    }

    /**
     * {@Inheritdoc}
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * {@Inheritdoc}
     */
    public function getRows(): array
    {
        return $this->getResults();
    }

    /**
     * {@Inheritdoc}
     */
    public function current(): array
    {
        return current($this->results);
    }

    /**
     * {@Inheritdoc}
     */
    public function next(): void
    {
        next($this->results);
    }

    /**
     * {@Inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->results);
    }

    /**
     * {@Inheritdoc}
     */
    public function valid(): bool
    {
        return $this->key() !== null;
    }

    /**
     * {@Inheritdoc}
     */
    public function rewind(): void
    {
        reset($this->results);
    }

    /**
     * {@Inheritdoc}
     */
    public function count(): int
    {
        $count = 0;
        foreach ($this->results as $element) {
            $count += $element["rowcount"];
        }
        return $count;
    }
}
