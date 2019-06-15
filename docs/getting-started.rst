.. _getting-started:

===============
Getting started
===============

Learn how to install and get started with the :ref:`CrateDB PDO driver
<index>`.

.. rubric:: Table of contents

.. contents::
   :local:

.. _prerequisites:

Prerequisites
=============

Your project must be using `Composer`_.

Set up as a dependency
======================

The driver is available as `a package`_.

Add the driver package to you project `composer.json`_ file, like this:

.. code-block:: json

    {
      "require": {
        "crate/crate-pdo":"~1.0.0"
      }
    }

.. _install:

Install
=======

Once the package has been configured as a dependency, you can install it, like
so::

    sh$ composer install

Afterwards, if you are not already doing so, you must require the Composer
`autoload.php`_ file. You can do this by adding a line like this to your PHP
application:

.. code-block:: php

    require __DIR__ . '/vendor/autoload.php';

.. SEEALSO::

   For more help with Composer, consult the `Composer documentation`_.

Next steps
==========

Learn how to :ref:`connect to CrateDB <connect>`.

.. _a package: https://packagist.org/packages/crate/crate-pdo
.. _autoload.php: https://getcomposer.org/doc/01-basic-usage.md#autoloading
.. _Composer documentation: https://getcomposer.org
.. _Composer: https://getcomposer.org/
.. _composer.json: https://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup
