<?php
/**
 * @author Antoine Hedgcock
 */

namespace Crate\PDO\ArtaxExt;

use Artax\Client as ArtaxClient;
use Artax\Request;
use Crate\PDO\PDOStatement;

class Client implements ClientInterface
{
    /**
     * @var PDOStatement|null
     */
    private $lastStatement = null;

    /**
     * @var string
     */
    private $uri;

    /**
     * @param string $uri
     * @param array  $options
     */
    public function __construct($uri, array $options)
    {
        $this->uri    = $uri;

        $this->client = new ArtaxClient();
        $this->client->setAllOptions($options);
    }

    /**
     * {@Inheritdoc}
     */
    public function execute(PDOStatement $statement, $query, array $parameters)
    {
        $this->lastStatement = $statement;

        $body = [
            'stmt' => $query,
            'args' => $parameters
        ];

        $request = new Request();
        $request->setUri($this->uri);
        $request->setMethod('POST');
        $request->setBody(json_encode($body));

        return $this->client->request($request);
    }

    /**
     * {@Inheritdoc}
     */
    public function getLastStatement()
    {
        return $this->lastStatement;
    }

    /**
     * @return array
     */
    public function getServerInfo()
    {
        // TODO: Implement getServerInfo() method.
    }

    /**
     * @return string
     */
    public function getServerVersion()
    {
        // TODO: Implement getServerVersion() method.
    }
}
