<?php
namespace Framework\Middleware;


use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RoutePrefixedMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var string
     */
    private $prefix;
    /**
     * @var string
     */
    private $middleware;

    public function __construct(
        ContainerInterface $container,
        string $prefix,
        string $middleware
    )
    {
        $this->container = $container;
        $this->prefix = $prefix;
        $this->middleware = $middleware;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if (strpos($path, $this->prefix) === 0) {

            return $this->container->get($this->middleware)->process($request, $handler);
        }

        return $handler->handle($request);
    }
}