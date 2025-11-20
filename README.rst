==================
CrateDB PDO Driver
==================

.. image:: https://github.com/crate/crate-pdo/workflows/Tests/badge.svg
    :target: https://github.com/crate/crate-pdo/actions?workflow=Tests
    :alt: Build status

.. image:: https://github.com/crate/crate-pdo/workflows/Docs/badge.svg
    :target: https://github.com/crate/crate-pdo/actions?workflow=Docs
    :alt: Build status (documentation)

.. image:: https://codecov.io/gh/crate/crate-pdo/branch/main/graph/badge.svg
    :target: https://app.codecov.io/gh/crate/crate-pdo
    :alt: Coverage

.. image:: https://scrutinizer-ci.com/g/crate/crate-pdo/badges/quality-score.png?b=main
    :target: https://scrutinizer-ci.com/g/crate/crate-pdo
    :alt: Quality

|

.. image:: https://poser.pugx.org/crate/crate-pdo/v/stable
    :target: https://packagist.org/packages/crate/crate-pdo
    :alt: Latest stable version

.. image:: https://img.shields.io/badge/PHP-7.3%2C%207.4%2C%208.0%2C%208.1%2C%208.2%2C%208.3%2C%208.4%2C%208.5%2C%208.6-green.svg
    :target: https://packagist.org/packages/crate/crate-pdo
    :alt: Supported PHP versions

.. image:: https://poser.pugx.org/crate/crate-pdo/d/monthly
    :target: https://packagist.org/packages/crate/crate-pdo
    :alt: Monthly downloads

.. image:: https://poser.pugx.org/crate/crate-pdo/license
    :target: https://packagist.org/packages/crate/crate-pdo
    :alt: License

|

The `PHP Data Objects (PDO)`_ extension defines a lightweight,
consistent interface for accessing databases in PHP.
The `CrateDB PDO Driver`_ provides a PDO adapter to the HTTP
interface of `CrateDB`_.

The adapter allows you to use the standard PDO API
when connecting to CrateDB from PHP applications.

Installation
============

The CrateDB PDO adapter is available as a Composer_ package.

    composer require crate/crate-pdo

See the `installation documentation`_ for more info.

Documentation
=============

The documentation for the ``crate-pdo`` package
is available at https://cratedb.com/docs/pdo/.

Contributing
============

This project is primarily maintained by `Crate.io`_, but community
contributions are very much welcome.
See the `developer docs`_ and the `contribution docs`_ for more
information about how to get started and how to contribute.

If you need a different support contact for contributions or
requests other than GitHub, please choose one of our other
`support channels`_.


.. _Composer: https://getcomposer.org/
.. _contribution docs: CONTRIBUTING.rst
.. _Crate.io: https://cratedb.com
.. _crate/crate-pdo: https://packagist.org/packages/crate/crate-pdo
.. _CrateDB: https://github.com/crate/crate
.. _CrateDB PDO Driver: https://github.com/crate/crate-pdo
.. _developer docs: DEVELOP.rst
.. _installation documentation: https://cratedb.com/docs/pdo/en/latest/getting-started.html
.. _PDO: http://www.php.net/manual/en/intro.pdo.php
.. _PHP Data Objects (PDO): https://www.php.net/manual/en/intro.pdo.php
.. _support channels: https://cratedb.com/support/
