(index)=

# CrateDB PDO Driver

The [PHP Data Objects (PDO)] extension defines a lightweight,
consistent interface for accessing databases in PHP.
The [CrateDB PDO driver] provides a PDO adapter to the HTTP
interface of [CrateDB].

:::{rubric} Synopsis
:::

```php
<?php

require 'vendor/autoload.php';

use Crate\PDO\PDOCrateDB as PDO;

$pdo = new PDO(
    'crate:localhost:4200',
    'crate',
    'crate',
    [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC],
);

$stm = $pdo->query('SELECT * FROM sys.summits ORDER BY height DESC LIMIT 3');
while ($row = $stm->fetch()) {
    print_r($row);
}
?>
```

:::{rubric} Documentation
:::

```{toctree}
:maxdepth: 1

getting-started
connect
data-types
```

:::{rubric} See also
:::

- The [CrateDB PDO example application] demonstrates the use of the
  CrateDB PDO driver.
- An alternative to the HTTP driver is to use the [PostgreSQL PDO Driver],
  demonstrated at [PostgreSQL PDO example application].


[CrateDB]: https://crate.io/products/cratedb/
[CrateDB PDO driver]: https://github.com/crate/crate-pdo
[CrateDB PDO example application]: https://github.com/crate/crate-sample-apps/tree/main/php-slim
[PHP Data Objects (PDO)]: https://www.php.net/manual/en/intro.pdo.php
[PostgreSQL PDO Driver]: https://www.php.net/manual/en/ref.pdo-pgsql.php
[PostgreSQL PDO example application]: https://github.com/crate/cratedb-examples/tree/main/by-language/php-pdo
