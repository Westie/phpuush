<?php

// set up some constants first, because some of my friends are probably going
// to be setting this up in a manner that is "security conscious" - moving the
// app directory outside of this folder and into its parent somewhere
define('APP_DIR', realpath(__DIR__ . '/app') . '/');

// require composer
require APP_DIR . 'vendor/autoload.php';

use League\Container\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// set up service provider
$container = new Container();
$container->addServiceProvider(App\ServiceProvider::class);

// start messing about with slim
$app = AppFactory::createFromContainer($container);

$app->get('/dl', function (Request $request, Response $response, array $arguments) {
	$response->getBody()->write('85');
	return $response;
});

$app->group('/api', App\Router\Api::class);
$app->group('/page', App\Router\Page::class);

$app->any('/{alias:.*}', new App\Router\FileRoute($app));

$afterMiddleware = function ($request, $handler) {
    try {
        $response = $handler->handle($request);
    } catch (UnexpectedValueException $exception) {
        return (new Slim\Psr7\Response())->withStatus(404);
    } catch (Throwable $exception) {
        throw $exception;
        return (new Slim\Psr7\Response())->withStatus(500);
    }

    return $response;
};

$app->add($afterMiddleware);
$app->run();
