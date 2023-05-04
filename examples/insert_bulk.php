<?php
/*
 * Example demonstrating how to use CrateDB's bulk operations interface for
 * inserting large amounts of data efficiently, using PHP PDO.
 *
 * Prerequisites:
 *
 *  docker run --rm -it --publish=4200:4200 crate
 *
 * Synopsis:
 *
 *  php examples/insert_bulk.php
 */

declare(strict_types=1);

include("./vendor/autoload.php");

error_reporting(E_ALL);

// Connect to CrateDB.
use Crate\PDO\PDOCrateDB;

$connection = new PDOCrateDB("crate:localhost:4200", "crate");

// Create database table.
$connection->exec("DROP TABLE IF EXISTS test_table;");
$connection->exec("CREATE TABLE test_table (id INTEGER, name STRING, int_type INTEGER);");

// Run insert operation.
$parameters = [[5, 'foo', 1], [6, 'bar', 2], [7, 'foo', 3], [8, 'bar', 4]];
$statement = $connection->prepare(
    'INSERT INTO test_table (id, name, int_type) VALUES (?, ?, ?)',
    array("bulkMode" => true));
$statement->execute($parameters);

// Evaluate response.
// MUST use `PDO::FETCH_NUM` for returning bulk operation responses.
print("Total count: {$statement->rowCount()}\n");
$response = $statement->fetchAll(PDO::FETCH_NUM);
print_r($response);

// Disconnect from database.
// https://www.php.net/manual/en/pdo.connections.php
// https://stackoverflow.com/questions/18277233/pdo-closing-connection
$statement = null;
$connection = null;
