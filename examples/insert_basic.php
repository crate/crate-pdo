<?php
/*
 * Basic example demonstrating how to connect to CrateDB using PHP PDO.
 *
 * Prerequisites:
 *
 *  docker run --rm -it --publish=4200:4200 crate
 *
 * Synopsis:
 *
 *  php examples/insert_basic.php
 */

declare(strict_types=1);

include("./vendor/autoload.php");

error_reporting(E_ALL ^ E_DEPRECATED);

// Connect to CrateDB.
use Crate\PDO\PDO as CratePDO;
$connection = new CratePDO("crate:localhost:4200", "crate");

// Create database table.
$connection->exec("DROP TABLE IF EXISTS test_table;");
$connection->exec("CREATE TABLE test_table (id INTEGER, name STRING, int_type INTEGER);");

// Run insert operation.
$statement = $connection->prepare('INSERT INTO test_table (id, name, int_type) VALUES (?, ?, ?)');
$statement->execute([5, 'foo', 1]);
$statement->execute([6, 'bar', 2]);

// Evaluate response.
print("Total count: {$statement->rowCount()}\n");
$response = $statement->fetchAll(PDO::FETCH_NUM);
print_r($response);

// Disconnect from database.
// https://www.php.net/manual/en/pdo.connections.php
// https://stackoverflow.com/questions/18277233/pdo-closing-connection
$statement = null;
$connection = null;
