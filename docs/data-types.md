(data-types)=

# Data types

(type-map)=

## Type mapping

### Returned values

When returning values, this driver maps CrateDB types to native PHP types:

| CrateDB Type                                                                                             | PHP Type                                                            |
| -------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------- |
| [boolean](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#boolean)           | [boolean](https://www.php.net/manual/en/language.types.boolean.php) |
| [byte](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data)         |                                                                     |
| [short](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data)        | [integer](https://www.php.net/manual/en/language.types.integer.php) |
| [integer](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data)      | [integer](https://www.php.net/manual/en/language.types.integer.php) |
| [long](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data)         | [integer](https://www.php.net/manual/en/language.types.integer.php) |
| [float](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data)        | [float](https://www.php.net/manual/en/language.types.float.php)     |
| [double](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data)       | [float](https://www.php.net/manual/en/language.types.float.php)     |
| [string](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#character-data)     | [string](https://www.php.net/manual/en/language.types.string.php)   |
| [ip](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#ip)                     | [string](https://www.php.net/manual/en/language.types.string.php)   |
| [timestamp](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#dates-and-times) | [integer](https://www.php.net/manual/en/language.types.integer.php) |
| [geo_point](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#geo-point)       | [array](https://www.php.net/manual/en/language.types.array.php)     |
| [geo_shape](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#geo-shape)       | [object](https://www.php.net/manual/en/language.types.object.php)   |
| [object](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#object)             | [object](https://www.php.net/manual/en/language.types.object.php)   |
| [array](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#array)               | [array](https://www.php.net/manual/en/language.types.array.php)     |

### Query values

When using values in a query, you must bind them to a `PDO` [parameter class
constant][parameter class constant], using either [bindParam()] or [bindValue()].

We recommend the following bindings:

| CrateDB Type                                                                                             | PDO Type               |
| -------------------------------------------------------------------------------------------------------- | ---------------------- |
| [boolean](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#boolean)           | `PDO::PARAM_BOOL`      |
| [byte](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data),        |                        |
| [short](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data),       | `PDO::PARAM_INT`       |
| [integer](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data)      | `PDO::PARAM_INT`       |
| [long](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data)         | `PDO::PARAM_LONG`      |
| [float](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data),       | `PDO::PARAM_FLOAT`     |
| [double](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#numeric-data)       | `PDO::PARAM_DOUBLE`    |
| [string](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#character-data)     | `PDO::PARAM_STR`       |
| [ip](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#ip)                     | `PDO::PARAM_STR`       |
| [timestamp](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#dates-and-times) | `PDO::PARAM_TIMESTAMP` |
| [geo_point](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#geo-point)       | `PDO::PARAM_ARRAY`     |
| [geo_shape](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#geo-shape)       | `PDO::PARAM_OBJECT`    |
| [object](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#object)             | `PDO::PARAM_OBJECT`    |
| [array](https://crate.io/docs/crate/reference/en/latest/general/ddl/data-types.html#array)               | `PDO::PARAM_ARRAY`     |

[bindparam()]: https://www.php.net/manual/en/pdostatement.bindparam.php
[bindvalue()]: https://www.php.net/manual/en/pdostatement.bindvalue.php
[parameter class constant]: https://www.php.net/manual/en/pdo.constants.php
