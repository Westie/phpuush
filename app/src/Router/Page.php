<?php

namespace App\Router;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteCollectorProxyInterface;

class Page
{
    /**
     *  Called when connecting an app to this controller provider
     */
    public function __invoke(RouteCollectorProxyInterface $app)
    {
        $app->get('/register', [ $this, 'register' ]);
    }
}
