=====================
Crate PDO development
=====================

Requirements
============
To be able to run the installation you need to first install vagrant (https://www.vagrantup.com/downloads.html)
and one if it's providers. Development has been done using VirtualBox (https://www.virtualbox.org/) but any provider
should work just as fine.


Installation
============
Download the project::

    git clone git@github.com:crate/crate-pdo.git

Start up the vagrant machine, when run for the first time it will also run the needed provisioning::

    vagrant up

Get composer & install dependencies::

    vagrant ssh
    cd /vagrant
    curl -sS https://getcomposer.org/installer | php
    ./composer.phar install


Running the tests
=================

Enter the vagrant machine by standing in the project root::

    vagrant ssh

Change directory to the mounted folder::

    cd /vagrant

Execute the tests::

    ./vendor/bin/phpunit --coverage-html ./report

Contributing
============

1. Fork the project
2. Create a pull request
