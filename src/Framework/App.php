<?php
namespace Framework;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class App
{
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        var_dump($uri[-1]);
        if (!empty($uri) && $uri[-1] === "/") {
            $response = new Response();
            $response = $response->withStatus(301);
            $response = $response->withHeader('Location', substr($uri, 0, -1));

            return $response;
        }
        if ($uri == '/blog') {
            return new Response(200, [], '<h1>Bienvenue sur le blog</h1>');
        }

        return new Response(404, [], '<h1>Error 404</h1>');
    }
}
