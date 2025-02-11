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

/**
 * @internal
 */
trait PDOStatementImplementationPhp8
{
    /**
     * @deprecated Use one of the fetch- or iterate-related methods.
     *
     * @param int   $mode
     * @param mixed ...$args
     *
     * @return true
     */
    public function setFetchMode(int $mode, ...$args): true
    {
        return $this->doSetFetchMode($mode, ...$args);
    }

    /**
     * @deprecated Use fetchAllNumeric(), fetchAllAssociative() or fetchFirstColumn() instead.
     *
     * @param int|null $mode
     * @param mixed    ...$args
     *
     * @return mixed[]
     */
    #[\ReturnTypeWillChange]
    public function fetchAll(?int $mode = null, ...$args)
    {
        return $this->doFetchAll($mode, ...$args);
    }

    /**
     * @param int|null          $mode
     * @param int|string|object $params
     *
     * @return bool
     */
    abstract public function doSetFetchMode($mode, $params = null);

    /**
     * @param int|null          $fetch_style
     * @param int|string|object $fetch_argument
     * @param mixed[]           $ctor_args
     *
     * @return mixed[]
     */
    abstract public function doFetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = null);
}
