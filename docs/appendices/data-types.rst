.. _data-types:

==========
Data types
==========

.. rubric:: Table of contents

.. contents::
   :local:

.. _type-map:

Type mapping
============

Returned values
---------------

When returning values, this driver maps CrateDB types to native PHP types:

============= ===========
CrateDB Type  PHP Type
============= ===========
`boolean`__   `boolean`__
`byte`__
`short`__     `integer`__
`integer`__   `integer`__
`long`__      `integer`__
`float`__     `float`__
`double`__    `float`__
`string`__    `string`__
`ip`__        `string`__
`timestamp`__ `integer`__
`geo_point`__ `array`__
`geo_shape`__ `object`__
`object`__    `object`__
`array`__     `array`__
============= ===========

Query values
------------

When using values in a query, you must bind them to a ``PDO`` `parameter class
constant`_, using either `bindParam()`_ or `bindValue()`_.

We recommend the following bindings:

============= ========================
CrateDB Type  PDO Type
============= ========================
`boolean`__   ``PDO::PARAM_BOOL``
`byte`__,
`short`__,    ``PDO::PARAM_INT``
`integer`__   ``PDO::PARAM_INT``
`long`__      ``PDO::PARAM_LONG``
`float`__,    ``PDO::PARAM_FLOAT``
`double`__    ``PDO::PARAM_DOUBLE``
`string`__    ``PDO::PARAM_STR``
`ip`__        ``PDO::PARAM_STR``
`timestamp`__ ``PDO::PARAM_TIMESTAMP``
`geo_point`__ ``PDO::PARAM_ARRAY``
`geo_shape`__ ``PDO::PARAM_OBJECT``
`object`__    ``PDO::PARAM_OBJECT``
`array`__     ``PDO::PARAM_ARRAY``
============= ========================

__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#boolean
__ https://secure.php.net/manual/en/language.types.boolean.php
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-types
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-types
__ https://secure.php.net/manual/en/language.types.integer.php
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-types
__ https://secure.php.net/manual/en/language.types.integer.php
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-types
__ https://secure.php.net/manual/en/language.types.integer.php
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-types
__ https://secure.php.net/manual/en/language.types.float.php
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-types
__ https://secure.php.net/manual/en/language.types.float.php
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#string
__ https://secure.php.net/manual/en/language.types.string.php
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#ip
__ https://secure.php.net/manual/en/language.types.string.php
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#timestamp
__ https://secure.php.net/manual/en/language.types.integer.php
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#geo-point
__ https://secure.php.net/manual/en/language.types.array.php
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#geo-shape
__ https://secure.php.net/manual/en/language.types.object.php
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#object
__ https://secure.php.net/manual/en/language.types.object.php
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#array
__ https://secure.php.net/manual/en/language.types.array.php

__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#boolean
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-types
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-types
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-types
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-types
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-types
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-types
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#string
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#ip
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#timestamp
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#geo-point
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#geo-shape
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#object
__ https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#array

.. _bindParam(): http://de2.php.net/manual/en/pdostatement.bindparam.php
.. _bindValue(): http://de2.php.net/manual/en/pdostatement.bindvalue.php
.. _parameter class constant: http://de2.php.net/manual/en/pdo.constants.php
