=====
Usage
=====

.. rubric:: Table of Contents

.. contents::
   :local:

First Steps
===========

The general `PDO API Documentation`_ also applies to CrateDB PDO.
However, CrateDB PDO does not fully implement the specification yet.

Connect to CrateDB
==================

To connect to your cluster, CrateDB follows standard PDO syntax to form a data
source name string (DSN_) and then connect to it.

.. code-block:: php

  require 'vendor/autoload.php';
  use Crate\PDO\PDO as PDO;

  $dsn = 'crate:<HOSTNAME_OR_IP>:<PORT>';
  $connection = new PDO($dsn, null, null, null);

As CrateDB doesn't support authentication, the other parameters can be left
null.

DSN
---

Following DSN is supported::

    crate:<HOSTNAME_OR_IP>:<PORT>[,<HOSTNAME_OR_IP>:<PORT>,...][/<SCHEMA>]

Examples::

    crate:localhost:4200
    crate:127.0.0.1:4200
    crate:c1.example.com:4200,c2.example.com:4200,c3.example.com:4200
    crate:demo.crate.io:4200/my_schema
    crate:demo1.crate.io:4200,demo2.crate.io:4200/my_schema

The ``/schema`` part in the connection string is optional and can be omitted.
If no schema is provided the CrateDB's default schema ``doc`` is used.

Note that you can still implicitly provide a schema in SQL statements, e.g.:

.. code-block:: psql

  SELECT * FROM other_schema.my_table LIMIT 10;

.. note::

    Providing a default schema is only supported in ``CrateDB >= 0.55`` with
    ``crate-pdo >= 0.4``. Due to this it gets ignored if a lower version is
    used.


Timeout
=======

Crate-PDO uses the `CrateDB HTTP protocol`_. Setting the PDO attribute
``PDO::ATTR_TIMEOUT`` will raise a timeout exception and cancel the HTTP request
after the specified time has elapsed. Cancelling the HTTP connection, however,
does not cancel/kill the executed statement on the server.

The following setting specifies the request timeout duration in seconds:

**PDO::ATTR_TIMEOUT** (int)
    | *Default-Value:*    ``0`` (indefinitely)


Fetch Modes
===========

CrateDB PDO only supports a subset of the PDO fetch modes.

Available fetch modes are:

For ``PDOStatement::fetchAll``:

- ``PDO::FETCH_NUM``
- ``PDO::FETCH_NAMED``
- ``PDO::FETCH_ASSOC``
- ``PDO::FETCH_BOTH``
- ``PDO::FETCH_FUNC``
- ``PDO::FETCH_COLUMN``

For ``PDOStatement::fetch``:

- ``PDO::FETCH_NAMED``
- ``PDO::FETCH_ASSOC``
- ``PDO::FETCH_BOTH``
- ``PDO::FETCH_BOUND``
- ``PDO::FETCH_NUM``


CrateDB specific PDO attributes
===============================

The following attributes are CrateDB specific and used to set an attribute on
the database handle (see `PDO::setAttribute`_).

**PDO::CRATE_ATTR_HTTP_BASIC_AUTH** (string[])
    | *Value:*    ``[username, password]``

    Specifies the basic HTTP access authentication headers to provide a
    user name and password when making a request.

**PDO::CRATE_ATTR_DEFAULT_SCHEMA** (string)
    | *Default-Value:*    ``doc``

    Set the default schema for the PDO connection.

Custom Types
============

An example of inserting a custom type (`array`_) using the ``bindValue()``
method on the prepared statement is given below. For creating a new connection
please refer to `Connect to CrateDB`_.

.. code-block:: php

    $data = [1, 2];
    $stmt = $connection->prepare('INSERT INTO custom_objects (col_array) VALUES(?)');
    $stmt->bindValue($data, PDO::PARAM_ARRAY);

For `array`_ and `geo_point`_ the PDO constant ``PDO::PARAM_ARRAY`` is  used
while for `object`_ and `geo_shape`_ the type ``PDO:PARAM_OBJECT`` is used.

.. _`array`: https://crate.io/docs/reference/sql/data_types.html#array
.. _`object`: https://crate.io/docs/reference/sql/data_types.html#object
.. _`geo_point`: https://crate.io/docs/reference/sql/data_types.html#geo-point
.. _`geo_shape`: https://crate.io/docs/reference/sql/data_types.html#geo-shape
.. _`PDO API Documentation`: http://www.php.net/pdo
.. _DSN: https://en.wikipedia.org/wiki/Data_source_name
.. _`PDO::setAttribute`: http://php.net/manual/en/pdo.setattribute.php
.. _`CrateDB HTTP protocol`: https://crate.io/docs/reference/en/latest/protocols/http.html
