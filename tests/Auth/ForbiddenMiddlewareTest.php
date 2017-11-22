<?php
namespace Tests\App\Auth;

use App\Auth\ForbiddenMiddleware;
use Framework\Auth\ForbbidenException;
use Framework\Auth\User;
use Framework\Session\ArraySession;
use Framework\Session\SessionInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Interop\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ForbiddenMiddlewareTest extends TestCase
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function setUp()
    {
        $this->session = new ArraySession();
    }

    public function makeRequest($path = '/')
    {
        $uri = $this->getMockBuilder(Uri::class)->getMock();
        $uri->method('getPath')->willReturn($path);
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $request->method('getUri')->willReturn($uri);

        return $request;
    }

    public function makeRequestHandler()
    {
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();

        return $handler;
    }

    public function makeMiddleware()
    {
        return new ForbiddenMiddleware('/login', $this->session);
    }

    public function testCatchException()
    {
        $handler = $this->makeRequestHandler();
        $handler->expects($this->once())->method('handle')->willThrowException(new ForbbidenException());
        $response = $this->makeMiddleware()->process($this->makeRequest('/test'), $handler);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/login'], $response->getHeader('Location'));
        $this->assertEquals('/test', $this->session->get('auth.redirect'));
    }

    public function testCatchTypeErrorException()
    {
        $handler = $this->makeRequestHandler();
        $handler->expects($this->once())->method('handle')->willReturnCallback(function (User $user) {
            return true;
        });
        $response = $this->makeMiddleware()->process($this->makeRequest('/test'), $handler);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/login'], $response->getHeader('Location'));
        $this->assertEquals('/test', $this->session->get('auth.redirect'));
    }

    public function testBubleError()
    {
        $handler = $this->makeRequestHandler();
        $handler->expects($this->once())->method('handle')->willReturnCallback(function () {
            throw new \TypeError("test", 200);
        });

        try {
            $this->makeMiddleware()->process($this->makeRequest('/test'), $handler);
        } catch (\TypeError $e) {
            $this->assertEquals("test", $e->getMessage());
            $this->assertEquals(200, $e->getCode());
        }
    }


    public function testProcessValidRequest()
    {
        $handler = $this->makeRequestHandler();
        $respose = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $handler->expects($this->once())->method('handle')->willReturn($respose);

        $this->assertSame($respose, $this->makeMiddleware()->process($this->makeRequest('/test'), $handler));
    }
}