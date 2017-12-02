<?php
namespace Framework;

use DI\ContainerBuilder;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\FilesystemCache;
use Framework\Middleware\CombinedMiddleware;
use Framework\Middleware\RoutePrefixedMiddleware;
use Framework\Middleware\RouterMiddleware;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class App implements RequestHandlerInterface
{
    /**
     * List of modules
     * @var array
     */
    private $modules = [];
    /**
     * @var string[]
     */
    private $middlewares = [];
    /**
     * @var int
     */
    private $index = 0;
    /**
     * @var string|null|array
     */
    private $definition;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct($definition = null)
    {
        $this->definition = $definition;
    }

    /**
     * Rajoute un module a l application
     * @param string $module
     * @return App
     */
    public function addModule(string $module): self
    {
        $this->modules[] = $module;

        return $this;
    }

    /**
     * Rajoute un oomportement au niveau de la requete
     *
     * @param string|callable|MiddlewareInterface $routePrefix
     * @param string|callable|MiddlewareInterface|null $middleware
     * @return App
     */
    public function pipe(string $routePrefix, $middleware = null): self
    {
        if ($middleware === null) {
            $this->middlewares[] = $routePrefix;
        } else {
            $this->middlewares[] = new RoutePrefixedMiddleware($this->getContainer(), $routePrefix, $middleware);
        }

        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
//        $middleware = $this->getMiddleware();
//        if (is_null($middleware)) {
//            throw new \Exception('Aucun middleware n\' intercepte cette requete');
//        } elseif (is_callable($middleware)) {
//            return call_user_func_array($middleware, [$request, [$this, 'handle']]);
//        } elseif ($middleware instanceof MiddlewareInterface) {
//            return $middleware->process($request, $this);
//        }
        $this->index++;
        if ($this->index > 1) {
            throw new \Exception();
        }
        $middleware = new CombinedMiddleware($this->getContainer(), $this->middlewares);

        return $middleware->process($request, $this);
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        foreach ($this->modules as $module) {
            $this->getContainer()->get($module);
        }

        return $this->handle($request);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            $builder = new ContainerBuilder();
            $env = getenv('ENV') ?: 'production';
            if ($env === 'production') {
                $builder->setDefinitionCache(new FilesystemCache('tmp/di'));
                $builder->writeProxiesToFile(true, 'tmp/proxies');
            }
            if ($this->definition) {
                $builder->addDefinitions($this->definition);
            }
            foreach ($this->modules as $module) {
                if ($module::DEFINITION) {
                    $builder->addDefinitions($module::DEFINITION);
                }
            }
            $this->container = $builder->build();
        }
        return $this->container;
    }

//    private function getMiddleware()
//    {
//        if (array_key_exists($this->index, $this->middlewares)) {
//            if (is_string($this->middlewares[$this->index])) {
//                $middleware = $this->container->get($this->middlewares[$this->index]);
//            } else {
//                $middleware = $this->middlewares[$this->index];
//            }
//
//            $this->index++;
//
//            return $middleware;
//        }
//        return null;
//    }

    /**
     * @return array
     */
    public function getModules(): array
    {
        return $this->modules;
    }
}
