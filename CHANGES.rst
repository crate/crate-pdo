 CHANGES for crate-pdo
=======================

Unreleased
==========

- Added support for PHP 8.0

- Removed support for PHP 7.2, it has reached end of life

- Bumped required guzzle http client dependency to ``^7.2``

2020/09/28 1.1.0
================

- Bumped required guzzle http client dependency to ``~7.0``

2019/04/09 1.0.1
================

- Fixed boolean parsing when binding a ``PDO::PARAM_BOOL`` parameter value.

- Fixed `cast` exception when binding a NULL value to a parameter which is not
  of type ``PDO::PARAM_NULL``.

2018/04/04 1.0.0
================

- Added support for SSL via `PDO::CRATE_ATTR_SSL_MODE`

- Updated provisioning using ubuntu xenial64 and the latest version of crate

- BREAKING: Refactored the internal http server/client implementation to a
  simpler more contained version in the ``ServerPool``

- BREAKING: Upgraded the library to use php 7.2 features


2018/01/25 0.7.0
================

 - Add support for fetch style ``PDO::FETCH_OBJ``.

2017/07/17 0.6.3
================

 - Fix: binding NULL as first param in queries is now supported

2017/04/06 0.6.2
================

 - Fix: fetching the same column twice could cause an error

2017/02/06 0.6.1
================

 - Changed default request timeout to ``0`` (indefinitely)

2016/11/04 0.6.0
================

 - Expose ``getServerVersion()`` and ``getServerInfo()`` on the PDO implementation
   which return the version number of the Crate server connected to.

 - Fix: having the same named parameter multiple times in a prepared SQL
   statement caused incorrect parameter substitution with bound values

2016/07/01 0.5.1
================

 - Fixed an issue that occur if parameters are passed in a different order
   than specified in the sql statement.

2016/06/20 0.5.0
================

 - Updated dependency: guzzlehttp/guzzle to ~6.0
   WARNING: This is a backward incompatible change!

2016/01/18 0.4.0
================

 - Support for multiple hosts in DSN connection string

 - Added support for using a default schema in PDO connection
   via ``/schema`` suffix in connection string

2016/01/12 0.3.1
================

 - Verify support for PHP 7
   Updated composer.json to meet dependencies

2015/08/12 0.3.0
================

 - Support binding named parameters with a leading `:` character

2015/05/08 0.2.1
================

 - Set auth attribute in constructor of PDO class if credentials
   are available

2015/05/07 0.2.0
================

 - Support guzzle http basic auth at Crate/PDO through doctrine
   dbal connection user credetials

2015/01/08 0.1.0
================

 - Fix performance issues by switching http client library to
   ``guzzle``.

2014/12/18 0.0.7
================

 - Fix: Literals containing a `:` character were misinterpreted as
   named parameters.

 - Nailed dependency versions of amphp/artax and amphp/amp
   to prevent composer from fetching newer, incompatible releases

2014/12/04 0.0.6
================

 - Fix setting of the ``timeout`` attribute.

2014/11/27 0.0.5
================

 - Support crate `array` and `object` data types

 - Code style issues

2014/10/30 0.0.4
================

 - Using a common DSN format now instead of an URI. See README.rst for
   details.
   WARNING: This is a backward incompatible change!

2014/10/27 0.0.3
================

 - Added support for named parameters (required by dbal driver)

 - Fixed the way row count is calculated

2014/10/20 0.0.2
================

 - Update dependencies, `rdlowrey/artax` moved to `amphp/artax`

2014/09/09 0.0.1
================

 - Initial release
