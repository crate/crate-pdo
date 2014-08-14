<?php
/**
 * @author Antoine Hedgcock
 */

namespace Crate\PDO\ArtaxExt;

use Artax\Client as ArtaxClient;
use Artax\Request;
use Crate\PDO\Exception\RuntimeException;
use Crate\Stdlib\Collection;

class Client implements ClientInterface
{
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
    public function execute($query, array $parameters)
    {
        $body = [
            'stmt' => $query,
            'args' => $parameters
        ];

        $request = new Request();
        $request->setUri($this->uri);
        $request->setMethod('POST');
        $request->setBody(json_encode($body));

        $response     = $this->client->request($request);
        $responseBody = json_decode($response->getBody());

        if ($response->getStatus() !== 200) {

            $errorCode    = $responseBody->error->code;
            $errorMessage = $responseBody->error->message;

            throw new RuntimeException($errorMessage, $errorCode);
        }

        return new Collection(
            $responseBody->rows,
            $responseBody->cols,
            $responseBody->duration,
            $responseBody->rowcount
        );
    }

    /**
     * {@Inheritdoc}
     */
    public function getServerInfo()
    {
        // TODO: Implement getServerInfo() method.
    }

    /**
     * {@Inheritdoc}
     */
    public function getServerVersion()
    {
        // TODO: Implement getServerVersion() method.
    }

    /**
     * {@Inheritdoc}
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * {@Inheritdoc}
     */
    public function setTimeout($timeout)
    {
        $this->client->setOption('connectTimeout', $timeout);
    }
}
