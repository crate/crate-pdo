<?php
/**
 * @author Antoine Hedgcock
 */

namespace CrateTest\PDO\Http;

use Crate\PDO\Exception\RuntimeException;
use Crate\PDO\Exception\UnsupportedException;
use Crate\Stdlib\Collection;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Crate\PDO\Http\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Class ClientTest
 *
 * @coversDefaultClass \Crate\PDO\Http\Client
 * @covers ::<!public>
 *
 * @group unit
 */
class ClientTest extends PHPUnit_Framework_TestCase
{
    const DSN = 'http://localhost:4200';
    const SQL = 'SELECT * FROM test_table';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var GuzzleClient|PHPUnit_Framework_MockObject_MockObject
     */
    private $internalClient;

    /**
     * @covers ::__construct
     */
    protected function setUp()
    {
        $this->client         = new Client(static::DSN, []);
        $this->internalClient = $this->getMock(GuzzleClientInterface::class);

        $reflection = new ReflectionClass(Client::class);

        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->client, $this->internalClient);
    }

    /**
     * Create a response to be used
     *
     * @param int   $statusCode
     * @param array $body
     *
     * @return Response
     */
    private function createResponse($statusCode, array $body)
    {
        $body = Stream::factory(json_encode($body));

        return new Response($statusCode, [], $body);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithResponseFailure()
    {
        $code    = 1337;
        $message = 'hello world';

        $this->setExpectedException(RuntimeException::class, $message, $code);

        $request = $this->getMock(RequestInterface::class);
        $response = $this->createResponse(400, ['error' => ['code' => $code, 'message' => $message]]);

        $exception = ClientException::create($request, $response);

        $this->internalClient
            ->expects($this->once())
            ->method('post')
            ->will($this->throwException($exception));

        $this->client->execute(static::SQL, ['foo' => 'bar']);
    }

    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $body = [
            'cols'     => ['id', 'name'],
            'rows'     => [],
            'rowcount' => 0,
            'duration' => 0
        ];

        $response = $this->createResponse(200, $body);

        $this->internalClient
            ->expects($this->once())
            ->method('post')
            ->will($this->returnValue($response));

        $result = $this->client->execute(static::SQL, ['foo' => 'bar']);

        $this->assertInstanceOf(Collection::class, $result);
    }

    /**
     * @covers ::getServerInfo
     */
    public function testGetServerInfo()
    {
        $this->setExpectedException(UnsupportedException::class);
        $this->client->getServerInfo();
    }

    /**
     * @covers ::getServerVersion
     */
    public function testGetServerVersion()
    {
        $this->setExpectedException(UnsupportedException::class);
        $this->client->getServerVersion();
    }

    /**
     * @covers ::setTimeout
     */
    public function testSetTimeout()
    {
        $this->internalClient
            ->expects($this->once())
            ->method('setDefaultOption')
            ->with('timeout', 4);

        $this->client->setTimeout('4');
    }
}
