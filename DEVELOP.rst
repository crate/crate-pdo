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


Documentation
=============

The documentation is written using `Sphinx`_ and `ReStructuredText`_.


Working on the documentation
----------------------------

Python 3.7 is required.

Change into the ``docs`` directory:

.. code-block:: console

    $ cd docs

For help, run:

.. code-block:: console

    $ make

    Crate Docs Build

    Run `make <TARGET>`, where <TARGET> is one of:

      dev     Run a Sphinx development server that builds and lints the
              documentation as you edit the source files

      html    Build the static HTML output

      check   Build, test, and lint the documentation

      delint  Remove any `*.lint` files

      reset   Reset the build cache

You must install `fswatch`_ to use the ``dev`` target.


Continuous integration and deployment
-------------------------------------

|build| |travis| |rtd|

Travis CI is `configured`_ to run ``make check`` from the ``docs`` directory.
Please do not merge pull requests until the tests pass.

`Read the Docs`_ (RTD) automatically deploys the documentation whenever a
configured branch is updated.

To make changes to the RTD configuration (e.g., to activate or deactivate a
release version), please contact the `@crate/docs`_ team.


Archiving Docs Versions
-----------------------

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

To make changes to the RTD configuration (e.g., to activate or deactivate a
release version), please contact the `@crate/tech-writing`_ team.


.. _@crate/tech-writing: https://github.com/orgs/crate/teams/tech-writing
.. _Composer: https://getcomposer.org
.. _configured: https://github.com/crate/crate-pdo/blob/master/.travis.yml
.. _fswatch: https://github.com/emcrisostomo/fswatch
.. _IDE guide: https://gist.github.com/mikethebeer/d8feda1bcc6b6ef6ea59
.. _Read the Docs: http://readthedocs.org
.. _ReStructuredText: http://docutils.sourceforge.net/rst.html
.. _Sphinx: http://sphinx-doc.org/
.. _Vagrant: https://www.vagrantup.com/downloads.html
.. _versions hosted on ReadTheDocs: https://readthedocs.org/projects/crate-pdo/versions/
.. _VirtualBox: https://www.virtualbox.org/


.. |build| image:: https://img.shields.io/endpoint.svg?color=blue&url=https%3A%2F%2Fraw.githubusercontent.com%2Fcrate%2Fcrate-pdo%2Fmaster%2Fdocs%2Fbuild.json
    :alt: Build version
    :target: https://github.com/crate/crate-pdo/blob/master/docs/build.json

.. |travis| image:: https://img.shields.io/travis/crate/crate-pdo.svg?style=flat
    :alt: Travis CI status
    :target: https://travis-ci.org/crate/crate-pdo

.. |rtd| image:: https://readthedocs.org/projects/crate-pdo/badge/?version=latest
    :alt: Read The Docs status
    :target: https://readthedocs.org/projects/crate-pdo
