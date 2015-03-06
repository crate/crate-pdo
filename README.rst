.. image:: https://cdn.crate.io/web/2.0/img/crate-avatar_100x100.png
    :width: 100px
    :height: 100px
    :alt: Crate.IO
    :target: https://crate.io

.. image:: https://travis-ci.org/crate/crate-pdo.svg?branch=master
    :target: https://travis-ci.org/crate/crate-pdo
    :alt: Build status

.. image:: https://scrutinizer-ci.com/g/crate/crate-pdo/badges/coverage.png?b=master
    :target: https://scrutinizer-ci.com/g/crate/crate-pdo
    :alt: Coverage

.. image:: https://scrutinizer-ci.com/g/crate/crate-pdo/badges/quality-score.png?b=master
    :target: https://scrutinizer-ci.com/g/crate/crate-pdo
    :alt: Quality

Introduction
============

The goal of this project is to mimic a subset of the PDO api allowing
to use an existing API you're already familiar with when developing
with Crate.

Documentation / Supported fetch modes
=====================================

Since we are implementing an existing api the documentation available on
http://www.php.net/pdo also applies to this project.

On a side note, since we only support a subset of the PDO api only the
following fetch modes are supported.


For PDOStatement::fetchAll

- PDO::FETCH_NUM
- PDO::FETCH_NAMED
- PDO::FETCH_ASSOC
- PDO::FETCH_BOTH
- PDO::FETCH_FUNC
- PDO::FETCH_COLUMN

For PDOStatement::fetch

- PDO::FETCH_NAMED
- PDO::FETCH_ASSOC
- PDO::FETCH_BOTH
- PDO::FETCH_BOUND
- PDO::FETCH_NUM

Installation
============

Install the library by adding it to your composer.json or running::

    php composer.phar require crate/crate-pdo:~0.0.3

DSN
===

Following DSN is supported:
::

    crate:<HOSTNAME_OR_IP>:<PORT>

Example:
::

    crate:localhost:4200

Are you a Developer?
====================

You can help develop the crate-pdo adapter on your own with the latest
version hosted on GitHub.  To do so, please refer to ``DEVELOP.rst``
for further information.

Help & Contact
==============

Do you have any questions? Or suggestions? We would be very happy to
help you. So, feel free to swing by our IRC channel #crate on
Freenode_.  Or for further information and official contact please
visit `https://crate.io/ <https://crate.io/>`_.

.. _Freenode: http://freenode.net

License
=======

Copyright 2014-2015 CRATE Technology GmbH ("Crate")

Licensed to CRATE Technology GmbH ("Crate") under one or more contributor
license agreements.  See the NOTICE file distributed with this work for
additional information regarding copyright ownership.  Crate licenses
this file to you under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.  You may
obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.  See the
License for the specific language governing permissions and limitations
under the License.

However, if you have executed another commercial license agreement
with Crate these terms will supersede the license and you may use the
software solely pursuant to the terms of the relevant commercial agreement.
