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

use Crate\PDO\Exception\UnsupportedException;

/**
 * @internal
 */
trait PDOImplementationPhp8
{
    /**
     * @param string|null $query
     * @param int|null $fetchMode
     * @param mixed ...$fetchModeArgs
     * @return PDOStatement
     */
    public function query(?string $query = null, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement
    {
        if ($fetchMode !== null) {
            throw new UnsupportedException('PDOCrateDB::query $fetchMode not implemented yet');
        }
        // FIXME: return $this->doQuery($query, $fetchMode, ...$fetchModeArgs);
        return $this->doQuery($query);
    }

    /**
     * @param $statement
     * @return PDOStatement
     */
    abstract public function doQuery($statement);
}
