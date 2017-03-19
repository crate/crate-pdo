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

.. _Composer: https://getcomposer.org
.. _Vagrant: https://www.vagrantup.com/downloads.html
.. _VirtualBox: https://www.virtualbox.org/
.. _IDE guide: https://gist.github.com/mikethebeer/d8feda1bcc6b6ef6ea59