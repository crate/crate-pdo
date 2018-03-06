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
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class ServerTest
 *
 * @coversDefaultClass \Crate\PDO\Http\Server
 * @covers ::<!public>
 *
 * @group unit
 */
class ServerTest extends TestCase
{
    private const EMPTY_RESPONSE = [
        'rows'     => [],
        'cols'     => [],
        'duration' => 0,
        'rowcount' => 0,
    ];

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
        $this->client = $this->createMock(HttpClient::class);

        $reflection = new ReflectionClass($this->server);
        $property   = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->server, $this->client);
    }

    /**
     * @covers ::getServerInfo
     */
    public function testGetServerInfo()
    {
        $resp = [
            'rows'     => [['2.0.5']],
            'cols'     => ['version'],
            'duration' => 0,
            'rowcount' => 1,
        ];

        $this->client
            ->expects($this->once())
            ->method('__call')
            ->willReturn(new Response(200, [], json_encode($resp)));

        $this->server->getServerInfo();
    }

    /**
     * @covers ::getServerVersion
     */
    public function testGetServerVersion()
    {
        $resp = [
            'rows'     => [['2.0.5']],
            'cols'     => ['version'],
            'duration' => 0,
            'rowcount' => 1,
        ];

        $this->client
            ->expects($this->once())
            ->method('__call')
            ->willReturn(new Response(200, [], json_encode($resp)));

        $this->server->getServerVersion();
    }

    /**
     * @covers ::setTimeout
     */
    public function testSetTimeout()
    {
        $body = ['stmt' => 'select * from sys.cluster',
                 'args' => []];
        $args = [
            null, // uri
            ['json'    => $body,
             'headers' => [],
             'timeout' => 4,
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('__call')
            ->with('post', $args)
            ->willReturn(new Response(200, [], json_encode(self::EMPTY_RESPONSE)));

        $this->server->setTimeout('4');
        $this->server->execute('select * from sys.cluster');
    }

    /**
     * @covers ::setHTTPHeader
     */
    public function testSetHTTPHeader()
    {
        $schema       = 'my_schema';
        $schemaHeader = 'Default-Schema';
        $this->server->setHttpHeader($schemaHeader, $schema);

        $body = ['stmt' => 'select * from sys.cluster',
                 'args' => []];
        $args = [
            null, // uri
            ['json'    => $body,
             'headers' => [$schemaHeader => $schema],
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('__call')
            ->with('post', $args)
            ->willReturn(new Response(200, [], json_encode(self::EMPTY_RESPONSE)));

        $this->server->execute($body['stmt']);
    }

    public function testInitialOptions()
    {
        $this->server = new Server('http://localhost:4200/_sql', ['timeout' => 3]);
        $this->client = $this->createMock(HttpClient::class);

        $reflection = new ReflectionClass($this->server);
        $property   = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->server, $this->client);

        $body = ['stmt' => 'select * from sys.cluster',
                 'args' => []];
        $args = [
            null,
            ['json'    => $body,
             'headers' => [],
             'timeout' => 3,
            ],
        ];

        $this->client
            ->expects($this->once())
            ->method('__call')
            ->with('post', $args)
            ->willReturn(new Response(200, [], json_encode(self::EMPTY_RESPONSE)));

        $this->server->execute($body['stmt']);
    }
}
