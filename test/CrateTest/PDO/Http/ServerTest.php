<?php
/**
 * Created by IntelliJ IDEA.
 * User: christian
 * Date: 15/01/16
 * Time: 08:19
 */

namespace CrateTest\PDO\Http;

use Crate\PDO\Exception\UnsupportedException;
use Crate\PDO\Http\Server;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Class ServerTest
 *
 * @coversDefaultClass \Crate\PDO\Http\Server
 * @covers ::<!public>
 *
 * @group unit
 */
class ServerTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Server $client
     */
    private $server;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    /**
     * @covers ::__construct
     */
    protected function setUp()
    {
        $this->server = new Server('http://localhost:4200/_sql', []);
        $this->client = $this->getMock(HttpClientInterface::class);

        $reflection = new ReflectionClass($this->server);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->server, $this->client);
    }

    /**
     * @covers ::getServerInfo
     */
    public function testGetServerInfo()
    {
        $this->setExpectedException(UnsupportedException::class);
        $this->server->getServerInfo();
    }

    /**
     * @covers ::getServerVersion
     */
    public function testGetServerVersion()
    {
        $this->setExpectedException(UnsupportedException::class);
        $this->server->getServerVersion();
    }

    /**
     * @covers ::setTimeout
     */
    public function testSetTimeout()
    {
        $this->client
            ->expects($this->once())
            ->method('setDefaultOption')
            ->with('timeout', 4);

        $this->server->setTimeout('4');
    }

    /**
     * @covers ::setHTTPHeader
     */
    public function testSetHTTPHeader()
    {
        $schema = 'my_schema';
        $schemaHeader = 'Default-Schema';

        $this->client
            ->expects($this->once())
            ->method('setDefaultOption')
            ->with('headers/'.$schemaHeader, $schema);

        $this->server->setHttpHeader($schemaHeader, $schema);

        $server = new Server('http://localhost:4200/_sql', []);
        $reflection = new ReflectionClass($server);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);

        $server->setHttpHeader($schemaHeader, $schema);
        $internalClient = $property->getValue($server);
        $header = $internalClient->getDefaultOption('headers/'.$schemaHeader);

        $this->assertEquals($schema, $header);
    }

}