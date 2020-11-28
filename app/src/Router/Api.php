<?php

namespace App\Router;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteCollectorProxyInterface;

class Api
{
    /**
     *  Called when connecting an app to this controller provider
     */
    public function __invoke(RouteCollectorProxyInterface $app)
    {
        $app->get('/auth', [ $this, 'auth' ]);
        $app->post('/up', [ $this, 'up' ]);
        $app->get('/del', [ $this, 'del' ]);
        $app->get('/hist', [ $this, 'hist' ]);
        $app->get('/thumb', [ $this, 'thumb' ]);
    }

    /**
     *  Authentication
     *  
     *   - Request:
     *      + e = email address
     *      + p = password
     *      + k = api key
     *      + z = poop (what the...)
     *
     *   - Response (authenticated, success):
     *      > {premium},{apikey},[expire],{size-sum}
     *
     *   - Response (failure):
     *      > -1
     */
    public function auth(Request $request, Response $response, array $arguments): Response
    {
        return $response;
    }

    /**
     *  Upload a file
     *
     *   - Request:
     *      + k = apikey
     *      + c = hash of uploaded file, but don't check this
     *      + z = poop (what the...)
     *      + f = file
     *
     *   - Response (upload, success):
     *      > 0,{http://pointer/url},{id},{size}
     *
     *   - Response (failure):
     *      > -1
     */
    public function up(Request $request, Response $response, array $arguments): Response
    {
        return $response;
    }

    /**
     *  Deleting a file
     *
     *   - Request:
     *      + k = apikey
     *      + i = file identifier - on puush.me, is base10 of file hash
     *      + z = poop (what the...)
     *
     *   - Response (history, success):
     *      > 0
     *      > {id},{YYYY-MM-DD HH:MM:SS},{http://pointer/url},{filename.jpg},{views},{unknown}
     *
     *   - Response (failure):
     *      > -1
     */
    public function del(Request $request, Response $response, array $arguments): Response
    {
        return $response;
    }

    /**
     *  Get history
     *
     *   - Request:
     *      + k = apikey
     *
     *   - Response (history, success):
     *      > 0
     *      > {id},{YYYY-MM-DD HH:MM:SS},{http://pointer/url},{filename.jpg},{views},{unknown}
     *
     *   - Response (failure):
     *      > -1
     */
    public function hist(Request $request, Response $response, array $arguments): Response
    {
        return $response;
    }

    /**
     *  Generate a 100x100 thumbnail image
     *
     *   - Request:
     *      + k = apikey
     *      + i = file identifier - on puush.me, is base10 of file hash
     *
     *   - Response (success):
     *      image, resized
     *
     *   - Response (failure):
     *      > -1
     */
    public function thumb(Request $request, Response $response, array $arguments): Response
    {
        return $response;
    }
}
