(connect)=

# Connection options

(data-source-name)=

## Data source names

PDO makes use of [data source name] (DSN) strings.
A basic version of the DSN string for CrateDB might be:

```text
crate:<HOST_ADDR>:<PORT>
```

Here, replace `<HOST_ADDR>` with the host address of a CrateDB node, and
replace `<PORT>` with the port number of the [HTTP endpoint].
You can specify multiple CrateDB nodes separated by commas.

```text
crate:<HOST_ADDR_1>:<PORT>,<HOST_ADDR_2>:<PORT>
```

:::{NOTE}
The list of nodes is shuffled when the connection is first created.
For every query, the client will attempt to connect to each node in sequence
until a successful connection is made. Nodes are moved to the end of the
list each time they are tried.
Over multiple query executions, this behaviour functions as client-side
*round-robin* load balancing, which is analogous to [round-robin DNS].
:::

You can also specify a schema, like so:

```text
crate:<HOST_ADDR>:<PORT>/<SCHEMA>
```

Here, replace `<SCHEMA>` with the name of the schema you want to select.

:::{TIP}
The default CrateDB schema is `doc`, and if you do not specify a schema,
this is what will be used.
Note you can always query any schema you like by specifying it in the query
expression itself.
:::

To wrap up, here's a list of example DSN strings:

- `crate:localhost:4200`
- `crate:localhost:4200/my_schema`
- `crate:crate-1.vm.example.com:4200,crate-2.vm.example.com:4200`
- `crate:198.51.100.1:4200,198.51.100.2:4200/another_schema`

(authentication)=

## Authentication

Perform regular credentials-based authentication by passing username
and password to the constructor of the PDOCrateDB class.
{ref}`get-connection` has a full example.

Authentication was introduced in CrateDB versions 2.1.x.

If you are using CrateDB 2.1.x or later, you must supply a username. If you
are using earlier versions of CrateDB, this argument is not supported.

:::{SEEALSO}
See the {ref}`compatibility notes <cratedb-versions>` for more information.
:::

If you have not configured a custom [database user], you probably want to
authenticate as the CrateDB superuser, which is `crate`. The superuser
does not have a password, so you should omit the `password` argument.

## Advanced settings

Once you have a connection, you can configure settings with the
`setAttribute` method, like this:

```php
$connection->setAttribute(<ATTRIBUTE>, <VALUE>);
```

Here, replace `<ATTRIBUTE>` with a reference to a `PDO` attribute class
constant, and replace `<VALUE>` with the value you want to set it to.

`PDO` attribute class constants look like this:

```php
PDO::ATTR_TIMEOUT
```

:::{SEEALSO}
Consult the PDO [setAttribute] documentation for a full list of `PDO`
attribute class constants.
:::

### Timeout

`PDO::ATTR_TIMEOUT` (int) seconds

: The connection timeout.

  Setting this attribute will raise a timeout exception and cancel the [HTTP
  connection][http connection] after the specified duration has elapsed.

  Cancelling the HTTP connection, however, does not cancel the execution of the
  statement on the server.

  **Default:** `0` (indefinitely)

### Driver-specific constants

The CrateDB driver provides number of `PDO` attribute class constants.

`PDOCrateDB::CRATE_ATTR_DEFAULT_SCHEMA` (string)

: The default schema for the PDO connection.

  :::{TIP}
  The default CrateDB schema is `doc`, and if you do not specify a
  schema, this is what will be used.

  However, you can query any schema you like by specifying it in the query.
  :::

`PDOCrateDB::CRATE_ATTR_SSL_MODE` (int) named attribute

: The connection SSL mode. See also full example at {ref}`get-connection-ssl`.

  Accepted values:

  `CRATE_ATTR_SSL_MODE_DISABLED` (**default**)

  : Disable SSL mode.

  `CRATE_ATTR_SSL_MODE_ENABLED_BUT_WITHOUT_HOST_VERIFICATION`

  : Enable SSL mode, but do not perform host verification.

  `CRATE_ATTR_SSL_MODE_REQUIRED`

  : Enable SSL mode, and perform host verification.

`PDOCrateDB::CRATE_ATTR_SSL_KEY_PATH` (string)

: The path to an SSL client key file.

`PDOCrateDB::CRATE_ATTR_SSL_KEY_PASSWORD` (string)

: The SSL client key file password.

`PDOCrateDB::CRATE_ATTR_SSL_CERT_PATH` (string)

: The path to an SSL client certificate file.

`PDOCrateDB::CRATE_ATTR_SSL_CERT_PASSWORD` (string)

: The SSL client certificate file password.

`PDOCrateDB::CRATE_ATTR_SSL_CA_PATH` (string)

: The path to an SSL *Certificate Authority* (CA) certificate file.

:::{SEEALSO}
Consult the [CrateDB reference] for more help with setting up SSL.
:::

:::{NOTE}
When not configuring SSL but connecting to an SSL-enabled host, you will see an
error message like `cURL error 52: Empty reply from server`.
:::

## Fetch modes

CrateDB PDO supports a subset of the PDO [fetch modes], depending on the fetch
method used.

```{eval-rst}
+----------------------------+-----------------------+
| Fetch Method               | Supported Modes       |
+============================+=======================+
| ``PDOStatement::fetchAll`` | ``PDO::FETCH_NUM``    |
+                            +-----------------------+
|                            | ``PDO::FETCH_NAMED``  |
+                            +-----------------------+
|                            | ``PDO::FETCH_ASSOC``  |
+                            +-----------------------+
|                            | ``PDO::FETCH_BOTH``   |
+                            +-----------------------+
|                            | ``PDO::FETCH_FUNC``   |
+                            +-----------------------+
|                            | ``PDO::FETCH_COLUMN`` |
+                            +-----------------------+
|                            | ``PDO::FETCH_OBJ``    |
+----------------------------+-----------------------+
| ``PDOStatement::fetch``    | ``PDO::FETCH_NAMED``  |
+                            +-----------------------+
|                            | ``PDO::FETCH_ASSOC``  |
+                            +-----------------------+
|                            | ``PDO::FETCH_BOTH``   |
+                            +-----------------------+
|                            | ``PDO::FETCH_BOUND``  |
+                            +-----------------------+
|                            | ``PDO::FETCH_NUM``    |
+                            +-----------------------+
|                            | ``PDO::FETCH_OBJ``    |
+----------------------------+-----------------------+
```


[cratedb reference]: https://crate.io/docs/crate/reference/en/latest/admin/ssl.html
[data source name]: https://en.wikipedia.org/wiki/Data_source_name
[database user]: https://crate.io/docs/crate/reference/en/latest/admin/user-management.html
[fetch modes]: https://www.php.net/manual/en/pdostatement.fetch.php
[http connection]: https://crate.io/docs/crate/reference/en/latest/interfaces/http.html
[http endpoint]: https://crate.io/docs/crate/reference/en/latest/interfaces/http.html
[pdo documentation]: https://www.php.net/manual/en/intro.pdo.php
[round-robin dns]: https://en.wikipedia.org/wiki/Round-robin_DNS
[sample application]: https://github.com/crate/crate-sample-apps/tree/main/php-slim
[sample application documentation]: https://github.com/crate/crate-sample-apps/blob/main/php-slim/documentation.md
[setattribute]: https://www.php.net/manual/en/pdo.setattribute.php
