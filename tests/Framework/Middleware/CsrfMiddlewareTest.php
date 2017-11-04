<?php
namespace Tests\Framework\Middleware;

use Framework\Exception\CsrfInvalidException;
use Framework\Middleware\CsrfMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;

class CsrfMiddlewareTest extends TestCase
{
    /**
     * @var CsrfMiddleware
     */
    private $middleware;
    private $session;

    public function setUp()
    {
        $this->session = [];
        $this->middleware = new CsrfMiddleware($this->session);
    }

    public function testLetGetRequestPass()
    {
        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)
                               ->setMethods(['handle'])
                               ->getMock();
        $requestHandler->expects($this->once())
                       ->method('handle')
                       ->willReturn(new Response());
        $request = (new ServerRequest('GET', '/demo'));
        $this->middleware->process($request, $requestHandler);

    }

    public function testBlockPostRequestWithoutCsrf()
    {
        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->setMethods(['handle'])
            ->getMock();
        $requestHandler->expects($this->never())
            ->method('handle');
        $request = (new ServerRequest('POST', '/demo'));
        $this->expectException(CsrfInvalidException::class);
        $this->middleware->process($request, $requestHandler);
    }

    public function testLetPostRequestWithTokenPass()
    {
        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->setMethods(['handle'])
            ->getMock();
        $requestHandler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());
        $request = (new ServerRequest('POST', '/demo'));
        $token = $this->middleware->generateToken();
        $request = $request->withParsedBody(['_csrf' => $token]);
        $this->middleware->process($request, $requestHandler);
    }

    public function testBlockPostRequestWithInvalidCsrfToken()
    {
        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->setMethods(['handle'])
            ->getMock();
        $requestHandler->expects($this->never())
            ->method('handle');
        $request = (new ServerRequest('POST', '/demo'));
        $this->middleware->generateToken();
        $request = $request->withParsedBody(['_csrf' => 'hkjhkkhkjhk']);
        $this->expectException(CsrfInvalidException::class);
        $this->middleware->process($request, $requestHandler);
    }

    /**
     * testiramo da token samo jednom moze bit koristen
     */
    public function testLetPostRequestWithTokenPassOnce()
    {
        $requestHandler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->setMethods(['handle'])
            ->getMock();
        $requestHandler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());
        $request = (new ServerRequest('POST', '/demo'));
        $token = $this->middleware->generateToken();
        $request = $request->withParsedBody(['_csrf' => $token]);
        $this->middleware->process($request, $requestHandler);
        $this->expectException(CsrfInvalidException::class);
        $this->middleware->process($request, $requestHandler);
    }

    public function testLimitTheTokenNumber()
    {
        for($i = 0; $i < 100; $i++) {
            $token = $this->middleware->generateToken();
        }
        $this->assertCount(50, $this->session['csrf']);
        $this->assertEquals($token, $this->session['csrf'][49]);
    }
}