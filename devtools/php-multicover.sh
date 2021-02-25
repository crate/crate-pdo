#!/bin/sh
#
# About
# =====
#
# Run tests with coverage on both PHP7 and PHP8,
# merge coverage reports and render them as HTML.
#
# Please adjust the paths to the PHP interpreters
# to fit your needs. Make sure to `pecl install xdebug`
# in both PHP7 and PHP8 environments beforehand.
#
# Setup
# =====
#
# Install different PHP releases and Composer::
#
#   brew install php@7.3 php@7.4 php@8.0 brew-php-switcher composer
#
# Select PHP version::
#
#   brew-php-switcher 7.3
#   brew-php-switcher 7.4
#   brew-php-switcher 8.0
#
# Install `xdebug` extension into each environment for tracking code coverage::
#
#   pecl install xdebug
#
# Install `phpunit-merger`::
#
#   composer require --dev nimut/phpunit-merger
#
# Please make sure to remove it before committing as it is currently not available for PHP8::
#
#   composer remove --dev nimut/phpunit-merger
#

# Define shortcuts to executables.
php7=/usr/local/Cellar/php@7.4/7.4.15/bin/php
php8=/usr/local/Cellar/php/8.0.2/bin/php
phpunit="$(pwd)/vendor/bin/phpunit"
phpunit_merger="$(pwd)/vendor/bin/phpunit-merger"

# Prepare output directories.
mkdir -p build/multicover/reports build/multicover/html
rm -rf build/multicover/reports/* build/multicover/html/*

# Enable coverage tracing.
export XDEBUG_MODE=coverage

# Run tests with PHP coverage output on both PHP7 and PHP8.
echo Running tests with coverage on PHP7
$php7 $phpunit --coverage-php build/multicover/reports/clover-php7.php
echo; echo

echo Running tests with coverage on PHP8
$php8 $phpunit --coverage-php build/multicover/reports/clover-php8.php
echo; echo

# Merge coverage reports and generate HTML output.
echo Merging test reports
$php7 $phpunit_merger coverage build/multicover/reports --html=build/multicover/html /dev/null
