<?php
/**
 * @author Antoine Hedgcock
 */

namespace Crate\PDO\ArtaxExt;

use Artax\Response;
use Crate\PDO\PDOStatement;

interface ClientInterface
{
    /**
     * Execute the PDOStatement and return the response from server
     *
     * @param PDOStatement $statement
     * @param string       $queryString
     * @param array        $parameters
     *
     * @return Response
     */
    public function execute(PDOStatement $statement, $queryString, array $parameters);

    /**
     * Get the last PDOStatement that was used
     *
     * @return PDOStatement|null
     */
    public function getLastStatement();

    /**
     * @return array
     */
    public function getServerInfo();

    /**
     * @return string
     */
    public function getServerVersion();
}
