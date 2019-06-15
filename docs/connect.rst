.. _connect:

==================
Connect to CrateDB
==================

.. rubric:: Table of contents

.. contents::
   :local:

.. _data-source-name:

Data source names
=================

PDO makes use of `Data Source Name`_ (DSN) strings.

A basic version of the DSN string for CrateDB might be::

    crate:<HOST_ADDR>:<PORT>

Here, replace ``<HOST_ADDR>`` with the host address of a CrateDB node, and
replace ``<PORT>`` with the port number of the  `HTTP endpoint`_.

Example DSN strings:

- ``crate:localhost:4200``
- ``crate:crate-1.vm.example.com:4200``
- ``crate:198.51.100.1:4200``

You can specify a second CrateDB node, like so::

    crate:<HOST_ADDR_1>:<PORT>,<HOST_ADDR_2>:<PORT>

Here, ``<HOST_ADDR_1>`` and ``<HOST_ADDR_2>`` are the host strings for the
first and second CrateDB nodes, respectively.

In fact, you can specify as many nodes as you like. Each corresponding host
string must be separated from the previous one using a ``,`` character.

.. TIP::

   The list of nodes is shuffled when the connection is first created.

   For every query, the client will attempt to connect to each node in sequence
   until a successful connection is made. Nodes are moved to the end of the
   list each time they are tried.

   Over multiple query executions, this behaviour functions as client-side
   *round-robin* load balancing. (This is analogous to `round-robin DNS`_.)

You can also specify a schema, like so::

    crate:<HOST_ADDR>:<PORT>/<SCHEMA>

Here, replace ``<SCHEMA>`` with the name of the schema you want to select.

.. TIP::

   The default CrateDB schema is ``doc``, and if you do not specify a schema,
   this is what will be used.

   However, you can query any schema you like by specifying it in the query.

So, to wrap up, here's a more complex list of example DSN strings:

- ``crate:localhost:4200/my_schema``
- ``crate:crate-1.vm.example.com:4200,crate-2.vm.example.com:4200``
- ``crate:198.51.100.1:4200,198.51.100.2:4200/another_schema``

.. _get-connection:

Get a connection
================

You can get a PDO connection like this:

.. code-block:: php

   use Crate\PDO\PDO as PDO;

   $dsn = '<DATA_SOURCE_NAME>';
   $user = 'crate';
   $password = null;
   $options = null;
   $connection = new PDO($dsn, $user, $password, $options);

.. NOTE::

   Authentication was introduced in CrateDB versions 2.1.x.

   If you are using CrateDB 2.1.x or later, you must supply a username. If you
   are using earlier versions of CrateDB, this argument is not supported.

   See the :ref:`compatibility notes <cratedb-versions>` for more information.

   If you have not configured a custom `database user`_, you probably want to
   authenticate as the CrateDB superuser, which is ``crate``. The superuser
   does not have a password, so you should omit the ``password`` argument.

Advanced settings
=================

Once you have a connection, you can configure settings with the
``setAttribute`` method, like this:

.. code-block:: php

    $connection->setAttribute(<ATTRIBUTE>, <VALUE>);

Here, replace ``<ATTRIBUTE>`` with the a reference to a ``PDO`` attribute class
constant, and replace ``<VALUE>`` with the value you want to set it to.

``PDO`` attribute class constants look like this:

.. code-block:: php

    PDO::ATTR_TIMEOUT

.. SEEALSO::

    Consult the PDO `setAttribute`_ documentation for a full list of ``PDO``
    attribute class constants.

Timeout
-------

``PDO::ATTR_TIMEOUT`` (int) seconds
  The connection timeout.

  Setting this attribute will raise a timeout exception and cancel the `HTTP
  connection`_ after the specified duration has elapsed.

  Cancelling the HTTP connection, however, does not cancel the execution of the
  statement on the server.

  **Default:** ``0`` (indefinitely)

Driver specific constants
-------------------------

The CrateDB driver provides number of ``PDO`` attribute class constants.

``PDO::CRATE_ATTR_DEFAULT_SCHEMA`` (string)
    The default schema for the PDO connection.

    .. TIP::

       The default CrateDB schema is ``doc``, and if you do not specify a
       schema, this is what will be used.

       However, you can query any schema you like by specifying it in the query.

``PDO::CRATE_ATTR_SSL_MODE`` (int) named attribute
   The connection SSL mode.

   Accepted values:

   ``CRATE_ATTR_SSL_MODE_DISABLED`` (**default**)
       Disable SSL mode.

   ``CRATE_ATTR_SSL_MODE_ENABLED_BUT_WITHOUT_HOST_VERIFICATION``
       Enable SSL mode, but do not perform host verification.

   ``CRATE_ATTR_SSL_MODE_REQUIRED``
       Enable SSL mode, and perform host verification.

``PDO::CRATE_ATTR_SSL_KEY_PATH`` (string)
   The path to an SSL client key file.

``PDO::CRATE_ATTR_SSL_KEY_PASSWORD`` (string)
   The SSL client key file password.

``PDO::CRATE_ATTR_SSL_CERT_PATH`` (string)
   The path to an SSL client certificate file.

``PDO::CRATE_ATTR_SSL_CERT_PASSWORD`` (string)
   The SSL client certificate file password.

``PDO::CRATE_ATTR_SSL_CA_PATH`` (string)
   The path to an SSL *Certificate Authority* (CA) certificate file.

.. SEEALSO::

    Consult the `CrateDB reference`_ for more help with setting up SSL.

Fetch modes
-----------

CrateDB PDO supports a subset of the PDO `fetch modes`_, depending on the fetch
method used.

+----------------------------+-----------------------+
| Fetch Method               | Supported Modes       |
+============================+=======================+
| ``PDOStatement::fetchAll`` | ``PDO::FETCH_NUM``    |
+                            +-----------------------+
|                            | ``PDO::FETCH_NAMED``  |
+                            +-----------------------+
|                            | ``PDO::FETCH_ASSOC``  |
+                            +-----------------------+
|                            | ``PDO::FETCH_BOTH``   |
+                            +-----------------------+
|                            | ``PDO::FETCH_FUNC``   |
+                            +-----------------------+
|                            | ``PDO::FETCH_COLUMN`` |
+                            +-----------------------+
|                            | ``PDO::FETCH_OBJ``    |
+----------------------------+-----------------------+
| ``PDOStatement::fetch``    | ``PDO::FETCH_NAMED``  |
+                            +-----------------------+
|                            | ``PDO::FETCH_ASSOC``  |
+                            +-----------------------+
|                            | ``PDO::FETCH_BOTH``   |
+                            +-----------------------+
|                            | ``PDO::FETCH_BOUND``  |
+                            +-----------------------+
|                            | ``PDO::FETCH_NUM``    |
+                            +-----------------------+
|                            | ``PDO::FETCH_OBJ``    |
+----------------------------+-----------------------+

Next steps
==========

Use the standard the `PDO documentation`_ documentation for the rest of your
setup process.

.. SEEALSO::

   Check out the `sample application`_ (and the corresponding `documentation`_)
   for a practical demonstration of this driver in use.

.. _Composer documentation: https://getcomposer.org
.. _Composer: https://getcomposer.org/
.. _CrateDB reference: https://crate.io/docs/crate/reference/en/latest/admin/ssl.html
.. _data source name: https://en.wikipedia.org/wiki/Data_source_name
.. _database user: https://crate.io/docs/crate/reference/en/latest/admin/user-management.html
.. _documentation: https://github.com/crate/crate-sample-apps/blob/master/php/documentation.md
.. _DSN: https://en.wikipedia.org/wiki/Data_source_name
.. _fetch modes: https://secure.php.net/manual/en/pdostatement.fetch.php
.. _HTTP connection: https://crate.io/docs/crate/reference/en/latest/interfaces/http.html
.. _HTTP endpoint: https://crate.io/docs/crate/reference/en/latest/interfaces/http.html
.. _PDO API Documentation: http://www.php.net/pdo
.. _PDO documentation: http://www.php.net/manual/en/intro.pdo.php
.. _PDO::setAttribute: http://php.net/manual/en/pdo.setattribute.php
.. _round-robin DNS: https://en.wikipedia.org/wiki/Round-robin_DNS
.. _sample application: https://github.com/crate/crate-sample-apps/tree/master/php
.. _setAttribute: https://secure.php.net/manual/en/pdo.setattribute.php
