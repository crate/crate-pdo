=====
Usage
=====

First Steps
===========

The general `PDO API Documentation`_ also applies to Crate PDO.
However, Crate PDO does not fully implement the specification yet.

Connect to Crate
================

To connect to your cluster, Crate follows standard PDO syntax to form a data
source name string (DSN_) and then connect to it.

::

    require 'vendor/autoload.php';
    use Crate\PDO\PDO as PDO;

    $dsn = 'crate:<HOSTNAME_OR_IP>:<PORT>';
    $connection = new PDO($dsn, null, null, null);

As Crate doesn't support authentication, the other parameters can be left null.

DSN
---

Following DSN is supported::

    crate:<HOSTNAME_OR_IP>:<PORT>

Examples::

    crate:localhost:4200
    crate:127.0.0.1:4200
    crate:demo.crate.io:4200

Fetch Modes
===========

Crate PDO only supports a subset of the PDO fetch modes.

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


.. _`PDO API Documentation`: http://www.php.net/pdo
.. _DSN: https://en.wikipedia.org/wiki/Data_source_name
