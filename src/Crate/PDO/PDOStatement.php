<?php
// @todo license headers to be added

namespace Crate\PDO;

use ArrayIterator;
use Crate\CrateConst;
use Crate\PDO\ArtaxExt\ClientInterface;
use IteratorAggregate;
use PDOStatement as BasePDOStatement;
use Traversable;

class PDOStatement extends BasePDOStatement implements IteratorAggregate
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string|null
     */
    private $errorCode = null;

    /**
     * @var string|null
     */
    private $errorMessage = null;

    /**
     * @var array|null
     */
    private $cols;

    /**
     * @var array|null
     */
    private $rows;

    /**
     * @var int|null
     */
    private $rowCount;

    /**
     * @var int|null
     */
    private $duration;

    /**
     * @var string
     */
    private $sql;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @param ClientInterface $client
     * @param string          $sql
     * @param array           $attributes
     */
    public function __construct(ClientInterface $client, $sql, array $attributes)
    {
        $this->sql        = $sql;
        $this->client     = $client;
        $this->attributes = $attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($input_parameters = null)
    {
        $response     = $this->client->execute($this, $this->sql, $this->parameters);
        $responseBody = json_decode($response->getBody());

        if ($response->getStatus() !== 200) {

            $this->errorCode    = $responseBody->error->code;
            $this->errorMessage = $responseBody->error->message;

            return false;
        }

        $this->cols     = $responseBody->cols;
        $this->rows     = $responseBody->rows;
        $this->duration = $responseBody->duration;
        $this->rowCount = isset($responseBody->rowcount) ? $responseBody->rowcount : null;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function bindParam(
        $parameter,
        & $variable,
        $data_type = PDO::PARAM_STR,
        $length = null,
        $driver_options = null
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function rowCount()
    {
        return $this->rowCount;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchColumn($column_number = 0)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = [])
    {
    }

    /**
     * {@inheritDoc}
     */
    public function fetchObject($class_name = "stdClass", $ctor_args = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function errorCode()
    {
        return $this->errorCode;
    }

    /**
     * {@inheritDoc}
     */
    public function errorInfo()
    {
        if ($this->errorCode === null) {
            return null;
        }

        switch ($this->errorCode)
        {
            case CrateConst::ERR_INVALID_SQL:
                $ansiErrorCode = 42000;
                break;

            default:
                $ansiErrorCode = 'Not available';
                break;
        }

        return [
            $ansiErrorCode,
            $this->errorCode,
            $this->errorMessage
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setAttribute($attribute, $value)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute($attribute)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function columnCount()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnMeta($column)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function setFetchMode($mode, $params = null)
    {
        $args = func_get_args();
    }

    /**
     * {@inheritDoc}
     */
    public function nextRowset()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function closeCursor()
    {
        throw new Exception\UnsupportedException;
    }

    /**
     * {@inheritDoc}
     */
    public function debugDumpParams()
    {
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *       <b>Traversable</b>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->rows);
    }
}
