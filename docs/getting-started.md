(getting-started)=

# Getting started

Learn how to install and get started with the {ref}`CrateDB PDO driver
<index>`.

(prerequisites)=

## Prerequisites

Your project must be using [Composer].

## Set up as a dependency

The driver is available as a package at [crate/crate-pdo].

Add the driver package to your project's [composer.json]:

```shell
composer require crate/crate-pdo
```

(install)=

## Install

Once the package has been configured as a dependency, you can install it, like:

```shell
composer install
```

Afterward, if you are not already doing so, you must require the Composer
[autoload.php] file. You can do this by adding a line like this to your PHP
application:

```php
require __DIR__ . '/vendor/autoload.php';
```

:::{SEEALSO}
For more help with Composer, consult the [Composer documentation].
:::

## Next steps

Learn how to {ref}`connect to CrateDB <connect>`.

[autoload.php]: https://getcomposer.org/doc/01-basic-usage.md#autoloading
[composer]: https://getcomposer.org/
[composer documentation]: https://getcomposer.org
[composer.json]: https://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup
[crate/crate-pdo]: https://packagist.org/packages/crate/crate-pdo
