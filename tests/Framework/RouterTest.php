<?php
namespace Tests\Framework;


use Framework\Router;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    private $router;

    public function setUp()
    {
        $this->router = new Router();
    }

    public function testGetMethod()
    {
        $request = new ServerRequest('get', '/blog');
        $this->router->get('/blog', function () {
            return 'hallo';
        }, 'blog');
        $route = $this->router->match($request);
        $this->assertEquals('blog', $route->getName());
        $this->assertEquals('hallo', call_user_func_array($route->getCallback(), [$request]));
    }

    public function testGetMethodIfRouteDoesnNotMatch()
    {
        $request = new ServerRequest('get', '/blog');
        $this->router->get('/blogaze', function () {
            return 'hallo';
        }, 'blog');
        $route = $this->router->match($request);
        $this->assertEquals(null, $route);
    }

    public function testGetMethodWihParameters()
    {
        $request = new ServerRequest('get', '/blog/mon-slug-8');
        $this->router->get('/blog', function () {
            return 'azezea';
        }, 'posts');
        $this->router->get('/blog/{slug:[a-z0-9\-]+}-{id:\d+}', function () {
            return 'hallo';
        }, 'post.show');
        $route = $this->router->match($request);
        $this->assertEquals('post.show', $route->getName());
        $this->assertEquals('hallo', call_user_func_array($route->getCallback(), [$request]));
        $this->assertEquals(['slug' => 'mon-slug', 'id' => '8'], $route->getParams());

        //Test invalid url
        $route = $this->router->match(new ServerRequest('GET', '/blog/mon_slug-8'));
        $this->assertEquals(null, $route);
    }

    public function testGenerateUri()
    {
        $this->router->get('/blog', function () {
            return 'azezea';
        }, 'posts');
        $this->router->get('/blog/{slug:[a-z0-9\-]+}-{id:\d+}', function () {
            return 'hallo';
        }, 'post.show');
        $uri =  $this->router->generateUri('post.show', ['slug' => 'mon-article', 'id' =>'8']);
        $this->assertEquals('/blog/mon-article-8', $uri);
    }


    public function testGenerateUriWithQueryParams()
    {
        $this->router->get('/blog', function () {
            return 'azezea';
        }, 'posts');
        $this->router->get('/blog/{slug:[a-z0-9\-]+}-{id:\d+}', function () {
            return 'hallo';
        }, 'post.show');
        $uri =  $this->router->generateUri(
            'post.show',
            ['slug' => 'mon-article', 'id' =>'8'],
            ['p' => 2]);
        $this->assertEquals('/blog/mon-article-8?p=2', $uri);
    }

}