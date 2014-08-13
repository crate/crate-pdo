<?php
/**
 * @author Antoine Hedgcock
 */

namespace Crate\PDO;

use Crate\PDO\ArtaxExt\ClientInterface;

interface PDOInterface
{
    public function getClient();
    public function setClient(ClientInterface $client);
    public function doRequest(PDOStatement $statement, $sql, array $parameters);
    public function prepare($statement, $options = null);
    public function beginTransaction();
    public function commit();
    public function rollback();
    public function inTransaction();
    public function exec($statement);
    public function query($statement);
    public function lastInsertId($name = null);
    public function errorCode();
    public function errorInfo();
    public function setAttribute($attribute, $value);
    public function getAttribute($attribute);
    public static function getAvailableDrivers();
}
