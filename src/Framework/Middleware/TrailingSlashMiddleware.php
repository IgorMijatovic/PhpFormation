<?php
namespace Framework\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

class TrailingSlashMiddleware
{
    // next est prochaine middleware a executer si ts vas bien
    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        $uri = $request->getUri()->getPath();
        if (!empty($uri) && $uri[-1] === "/") {
            $response = new Response();
            $response = $response->withStatus(301);
            $response = $response->withHeader('Location', substr($uri, 0, -1));
        }

        return $next($request);
    }
}
