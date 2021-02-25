###############
Developer Guide
###############


*************
Using Vagrant
*************


Prerequisites
=============

You will need Vagrant_ and one of its providers.

We currently use VirtualBox_ but any provider should work just as well.


Installation
============

Clone the project::

    git clone git@github.com:crate/crate-pdo.git
    cd crate-pdo

Start up the Vagrant machine::

    vagrant up

When run for the first time, it will also run the needed provisioning.

If you are using IntelliJ or PhpStorm IDE you can follow the `IDE guide`_ to
set up your remote interpreter and test environment.


Installing Dependencies
-----------------------

Get Composer_ and install the dependencies::

    vagrant ssh
    cd /vagrant
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    composer install

If the environment is outdated, you can upgrade it::

    composer update


Running the Tests
=================

You can run the tests like::

    vagrant ssh
    cd /vagrant

    # Run test suite
    composer run test

    # Run code style checks
    composer run style

    # Output coverage report as HTML
    composer run -- test --coverage-html ./report

    # Run specific tests
    composer run -- test --filter "testFetchColumn"



************
Using Docker
************


Installation
============

Install prerequisites::

    # Install different PHP releases and Composer.
    brew install php@7.3 php@7.4 php@8.0 brew-php-switcher composer

    # Select PHP version.
    brew-php-switcher 7.3
    brew-php-switcher 7.4
    brew-php-switcher 8.0

    # Install `xdebug` extension into each environment for tracking code coverage.
    pecl install xdebug

Get the sources::

    git clone git@github.com:crate/crate-pdo.git

Setup project dependencies::

    composer install


Running the Tests
=================

::

    # Run CrateDB
    docker run -it --rm \
        --mount type=bind,source=$PWD/test/provisioning/crate.yml,target=/crate/config/crate.yml \
        --mount type=bind,source=$PWD/test/provisioning/keystore,target=/vagrant/test/provisioning/keystore \
        --mount type=bind,source=$PWD/test/provisioning/truststore,target=/vagrant/test/provisioning/truststore \
        --publish 4200:4200 --publish 5432:5432 crate/crate:nightly

    # Run test suite on current/default version of PHP
    composer run test

    # Run tests on both PHP7 and PHP8 to get the full picture of coverage
    composer run multicover
    open build/multicover/html/index.html


Invoke code style checks
========================

::

    # Run code style checks
    composer run check-style

    # Some code style quirks can be automatically fixed
    composer run fix-style



****************************
Working on the documentation
****************************

- The documentation is written using `Sphinx`_ and `ReStructuredText`_.
- Python>=3.7 is required.

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

      reset   Reset the build cache

You must install `fswatch`_ to use the ``dev`` target.


Continuous integration and deployment
=====================================

CI is configured to run ``make check`` from the ``docs`` directory.

`Read the Docs`_ (RTD) automatically deploys the documentation whenever a
configured branch is updated.

To make changes to the RTD configuration (e.g., to activate or deactivate a
release version), please contact the `@crate/docs`_ team.


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

To make changes to the RTD configuration (e.g., to activate or deactivate a
release version), please contact the `@crate/tech-writing`_ team.


.. _@crate/tech-writing: https://github.com/orgs/crate/teams/tech-writing
.. _Composer: https://getcomposer.org
.. _fswatch: https://github.com/emcrisostomo/fswatch
.. _IDE guide: https://gist.github.com/mikethebeer/d8feda1bcc6b6ef6ea59
.. _Read the Docs: http://readthedocs.org
.. _ReStructuredText: http://docutils.sourceforge.net/rst.html
.. _Sphinx: http://sphinx-doc.org/
.. _Vagrant: https://www.vagrantup.com/downloads.html
.. _versions hosted on ReadTheDocs: https://readthedocs.org/projects/crate-pdo/versions/
.. _VirtualBox: https://www.virtualbox.org/
