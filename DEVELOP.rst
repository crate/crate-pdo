===============
Developer Guide
===============

Prerequisites
=============

You will need Vagrant_ and one of its providers.

We currently use VirtualBox_ but any provider should work just as well.

Installation
============

Clone the project::

    $ git clone git@github.com:crate/crate-pdo.git

Start up the Vagrant machine::

    $ cd crate-pdo
    $ vagrant up

When run for the first time, it will also run the needed provisioning.

If you are using IntelliJ or PhpStorm IDE you can follow the `IDE guide`_ to
set up your remote interpreter and test environment.

PHP Version
-----------

There are two PHP versions installed in the Vagrant box:

- 5.6.3
- 7.0.2

To activate PHP 5, run::

    $ sudo rm /usr/bin/php
    $ sudo ln -s /usr/bin/php5 /usr/bin/php

To activate PHP 7, run::

    $ sudo rm /usr/bin/php
    $ sudo ln -s /usr/bin/php7 /usr/bin/php

Installing Dependencies
-----------------------

Get Composer_ and install the dependencies::

    $ vagrant ssh
    $ cd /vagrant
    $ curl -sS https://getcomposer.org/installer | php
    $ ./composer.phar install

If the environment is outdated, you upgrade like so::

    $ ./composer.phar update

Running the Tests
=================

You can run the tests like so::

    $ vagrant ssh
    $ cd /vagrant
    $ ./vendor/bin/phpunit --coverage-html ./report

To run a single test you can use the `--filter` option::

    $ ./vendor/bin/phpunit --filter "testFetchColumn"

Building the docs
=================

Make ``virtualenv`` and install requirements using ``pip``::

    $ python3 -m venv env
    $ source env/bin/activate
    $ pip install -U crate-docs-theme

To build the docs run::

    $ sphinx-build -n -b html -E `pwd`/docs `pwd`/docs/out/html

Archiving Docs Versions
=======================

Check the `versions hosted on ReadTheDocs`_.

We should only be hosting the docs for `latest`, the last three minor release
branches of the last major release, and the last minor release branch
corresponding to the last two major releases.

For example:

- ``latest``
- ``0.6``
- ``0.5``
- ``0.4``

Because this project has not yet had a major release, as of yet, there are no
major releases before `0` to include in this list.

Sometimes you might find that there are multiple older releases that need to be
archived.

You can archive releases by selecting *Edit*, unselecting the *Active*
checkbox, and then saving.

.. _Composer: https://getcomposer.org
.. _Vagrant: https://www.vagrantup.com/downloads.html
.. _VirtualBox: https://www.virtualbox.org/
.. _IDE guide: https://gist.github.com/mikethebeer/d8feda1bcc6b6ef6ea59
.. _versions hosted on ReadTheDocs: https://readthedocs.org/projects/crate-pdo/versions/
