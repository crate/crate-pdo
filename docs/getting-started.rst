===============
Getting Started
===============

This page shows you how to get started with the :ref:`CrateDB PHP PDO client
library <index>`.

Prerequisites
=============

You need to be using PHP and `Composer`_.

Install
=======

Install the library by adding it manually to your ``composer.json``:

.. code-block:: json

   {
     "require": {
       "crate/crate-pdo":"~0.7.0"
     }
   }

Or add it directly via Composer:

.. code-block:: sh

   sh$ composer require crate/crate-pdo:~0.7.0

Then run ``composer install`` or ``composer update``.

Inside your PHP script you will need to require the autoload file:

.. code-block:: php

   <?php
   require 'vendor/autoload.php';
   ...

For more information how to use Composer, please refer to the
`Composer documentation`_.

Connect to CrateDB
==================

To connect to your cluster, CrateDB follows standard PDO syntax to form a data
source name string (`dsn <https://en.wikipedia.org/wiki/Data_source_name>`_)
and then connect to it.

.. code-block:: php

   require_once __DIR__.'/vendor/autoload.php';
   use Crate\PDO\PDO as PDO;

   $dsn = 'crate:<HOSTNAME_OR_IP>:<PORT>';
   $user = "crate";
   $password = null;
   $options = null;
   $connection = new PDO($dsn, $user, $password, $options);

Learning More
=============

Crate.io maintains a `sample PHP application`_ that uses this library, which
may be a good starting point as you learn to use it for the first time. And be
sure to check out out the `application documentation`_.

Browse the rest of the PDO client :ref:`reference documentation <index>` for
more information.

.. _application documentation: https://github.com/crate/crate-sample-apps/blob/master/php/documentation.md
.. _Composer documentation: https://getcomposer.org
.. _Composer: https://getcomposer.org/
.. _sample PHP application: https://github.com/crate/crate-sample-apps/tree/master/php
