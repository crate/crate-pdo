===================
CrateDB PDO Adapter
===================

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

.. image:: https://poser.pugx.org/crate/crate-pdo/v/stable
    :target: https://packagist.org/packages/crate/crate-pdo
    :alt: Latest stable version

.. image:: https://img.shields.io/badge/PHP-7.3%2C%207.4%2C%208.0%2C%208.1%2C%208.2%2C%208.3%2C%208.4%2C%208.5-green.svg
    :target: https://packagist.org/packages/crate/crate-pdo
    :alt: Supported PHP versions

.. image:: https://poser.pugx.org/crate/crate-pdo/d/monthly
    :target: https://packagist.org/packages/crate/crate-pdo
    :alt: Monthly downloads

.. image:: https://poser.pugx.org/crate/crate-pdo/license
    :target: https://packagist.org/packages/crate/crate-pdo
    :alt: License

|

The CrateDB PDO adapter is a CrateDB_ specific database driver implementation
of the PDO_ API.

This adapter allows you to use the standardized PDO API you're already familiar
with when developing PHP applications with a CrateDB database.

Prerequisites
=============

You need to be using PHP and Composer_.

Installation
============

The CrateDB PDO adapter is available as a Composer package. Install it like::

    composer require crate/crate-pdo

See the `installation documentation`_ for more info.

Contributing
============

This project is primarily maintained by `Crate.io`_, but we welcome community
contributions!

See the `developer docs`_ and the `contribution docs`_ for more information.

Help
====

Looking for more help?

- Read the `project docs`_
- Check out our `support channels`_

.. _Composer: https://getcomposer.org/
.. _contribution docs: CONTRIBUTING.rst
.. _Crate.io: https://crate.io
.. _crate/crate-pdo: https://packagist.org/packages/crate/crate-pdo
.. _CrateDB: https://github.com/crate/crate
.. _developer docs: DEVELOP.rst
.. _installation documentation: https://crate.io/docs/reference/pdo/installation.html
.. _PDO: http://www.php.net/manual/en/intro.pdo.php
.. _support channels: https://crate.io/support/
.. _project docs: https://crate.io/docs/reference/pdo/
