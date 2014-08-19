<?php
/**
 * @author Antoine Hedgcock
 */

namespace CrateTest\PDO\ArtaxExt;

use Artax\Client as ArtaxClient;
use Artax\Response;
use Crate\PDO\ArtaxExt\Client;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Class ClientTest
 *
 * @coversDefaultClass \Crate\PDO\ArtaxExt\Client
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
     * @var ArtaxClient|PHPUnit_Framework_MockObject_MockObject
     */
    private $internalClient;

    /**
     * @covers ::__construct
     */
    protected function setUp()
    {
        $this->client         = new Client(static::DSN, []);
        $this->internalClient = $this->getMock('Artax\Client');

        $reflection = new ReflectionClass('Crate\PDO\ArtaxExt\Client');

        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->client, $this->internalClient);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteWithResponseFailure()
    {
        $code    = 1337;
        $message = 'hello world';

        $this->setExpectedException('Crate\PDO\Exception\RuntimeException', $message, $code);

        $response = new Response();
        $response->setStatus(400);
        $response->setBody(json_encode(['error' => ['code' => $code, 'message' => $message]]));

        $this->internalClient
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue($response));

        $this->client->execute(static::SQL, ['foo' => 'bar']);
    }

    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $response = new Response();
        $response->setStatus(200);
        $response->setBody('{"cols":["id","name"],"rows":[],"rowcount":0,"duration":1}');

        $this->internalClient
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue($response));

        $result = $this->client->execute(static::SQL, ['foo' => 'bar']);

        $this->assertInstanceOf('Crate\Stdlib\Collection', $result);
    }

    /**
     * @covers ::getServerInfo
     */
    public function testGetServerInfo()
    {
        $this->setExpectedException('Crate\PDO\Exception\UnsupportedException');
        $this->client->getServerInfo();
    }

    /**
     * @covers ::getServerVersion
     */
    public function testGetServerVersion()
    {
        $this->setExpectedException('Crate\PDO\Exception\UnsupportedException');
        $this->client->getServerVersion();
    }

    /**
     * @covers ::setTimeout
     */
    public function testSetTimeout()
    {
        $this->internalClient
            ->expects($this->once())
            ->method('setOption')
            ->with('connectTimeout', 4);

        $this->client->setTimeout('4');
    }
}
