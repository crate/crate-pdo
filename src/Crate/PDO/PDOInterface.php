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

/*
 * PDO compatibility for PHP 8.x.
 *
 * Reason:
 *  - https://github.com/php/php-src/pull/6220
 *
 * Implementation derived and inspired from:
 *  - https://github.com/doctrine/dbal/pull/4347
 *  - https://www.drupal.org/project/drupal/issues/3109885
 *  - https://www.drupal.org/project/drupal/issues/3156881
 *  - https://www.drupal.org/node/3170913
 *
 */

declare(strict_types=1);

namespace Crate\PDO;

use const PHP_VERSION_ID;

/**
 * Interface PDOInterface
 *
 * Used for unit testing the PDOStatement
 *
 * @internal
 */
// @codeCoverageIgnoreStart
if (PHP_VERSION_ID >= 80000) {
    interface PDOInterface extends PDOInterfacePhp8
    {
    }
} else {
    // phpcs:disable
    interface PDOInterface extends PDOInterfacePhp7
    {
    }
}
// @codeCoverageIgnoreEnd
