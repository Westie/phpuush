<?php

namespace App\Router;

use App\Repository\User as UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteCollectorProxyInterface;

class Page
{
    private $container;

    /**
     *  Called when connecting an app to this controller provider
     */
    public function __invoke(RouteCollectorProxyInterface $app)
    {
        $this->container = $app->getContainer();

        $app->get('/register', [ $this, 'register' ]);
    }

    /**
     *  Register
     */
    public function register(Request $request, Response $response, array $arguments): Response
    {
        return $response;
    }
}
