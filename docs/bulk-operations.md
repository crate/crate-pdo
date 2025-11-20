(bulk-operations)=

# Bulk operations

With CrateDB {ref}`crate-reference:http-bulk-ops`, suitable for `INSERT`,
`UPDATE`, and `DELETE` statements, you can submit multiple records, aka.
batches, to CrateDB within a single operation.

By using this style of communication, both the client and the server will
not waste resources on building and decoding huge SQL statements, and data
will also propagate more efficiently between CrateDB cluster nodes.

To use this mode, the `PDOStatement` offers a corresponding `bulkMode` option.
When creating a statement instance with it, the `$parameters` data will be
obtained as a **list of records**, like demonstrated in the example below.

```php
// Define insert data.
$parameters = [[5, 'foo', 1], [6, 'bar', 2], [7, 'foo', 3], [8, 'bar', 4]];

// Invoke bulk insert operation.
$statement = $connection->prepare(
    'INSERT INTO test_table (id, name, int_type) VALUES (?, ?, ?)',
    array("bulkMode" => true));
$statement->execute($parameters);

// Get the affected rows of each bulk entry.
$response = $statement->fetchAll(PDO::FETCH_NUM);
print_r($response);
```

:::{NOTE}
To get the affected rows of each bulk entry, you **must** use the
`PDO::FETCH_NUM` fetch mode. Other fetch modes will not work
and raise errors in this context.
:::
