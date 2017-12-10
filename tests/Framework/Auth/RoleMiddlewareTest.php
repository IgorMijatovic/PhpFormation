<?php
namespace Framework\Auth;


use Framework\Auth;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class RoleMiddlewareTest extends TestCase
{
    private $middleware;

    private $auth;

    public function setUp()
    {
        $this->auth = $this->prophesize(Auth::class);
        $this->middleware = new RoleMiddleware(
            $this->auth->reveal(),
            'admin'
        );
    }

    public function testWithUnauthenticatedUser()
    {
        $this->auth->getUser()->willReturn(null);
        $this->expectException(Auth\ForbbidenException::class);
        $this->middleware->process(new ServerRequest('GET', '/demo'), $this->makeRequestHandler()->reveal());
    }

    public function testWithBadRole()
    {
        $user = $this->prophesize(User::class);
        $user->getRoles()->willReturn(['user']);
        $this->auth->getUser()->willReturn($user->reveal());
        $this->expectException(Auth\ForbbidenException::class);
        $this->middleware->process(new ServerRequest('GET', '/demo'), $this->makeRequestHandler()->reveal());
    }

    public function testWithGoodRole()
    {
        $user = $this->prophesize(User::class);
        $user->getRoles()->willReturn(['admin']);
        $this->auth->getUser()->willReturn($user->reveal());
        $requestHandler = $this->makeRequestHandler();
        $requestHandler->handle(Argument::any())->shouldBeCalled()->willReturn(new Response());
        $this->middleware->process(new ServerRequest('GET', '/demo'), $requestHandler->reveal());
    }

    private function makeRequestHandler(): ObjectProphecy
    {
        $requestHandler = $this->prophesize(RequestHandlerInterface::class);
        $requestHandler->handle(Argument::any())->willReturn(new Response());

        return $requestHandler;
    }
}