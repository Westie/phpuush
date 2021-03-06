<?php

// set up some constants first, because some of my friends are probably going
// to be setting this up in a manner that is "security conscious" - moving the
// app directory outside of this folder and into its parent somewhere
define('PHAR_MODE', class_exists('Phar') && Phar::running());

if (!defined('APP_DIR')) {
    if (PHAR_MODE) {
        define('APP_DIR', realpath(getcwd() . '/app'));
        define('APP_VENDOR_DIR', 'phar://' . basename(__DIR__) . '/app/vendor');
    } else {
        define('APP_DIR', realpath(__DIR__ . '/app'));
        define('APP_VENDOR_DIR', realpath(APP_DIR . '/vendor'));
    }
}

// require composer
require APP_VENDOR_DIR . '/autoload.php';

use League\Container\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

// set up service provider
$container = new Container();
$container->addServiceProvider(App\ServiceProvider::class);

// start messing about with slim
$app = AppFactory::createFromContainer($container);

$app->any('/dl', function (Request $request, Response $response, array $arguments) {
    $response->getBody()->write('85');
    return $response;
});

$app->any('/', function (Request $request, Response $response, array $arguments) {
    return $response->withStatus(200);
});

$app->group('/api', App\Router\Api::class);
$app->group('/page', App\Router\Page::class);
$app->any('/{alias:[\w\.]*?}', new App\Router\FileRoute($app));
$app->any('/{alias:[\w\.]*?}/{ext}', new App\Router\FormattedFileRoute($app));

$afterMiddleware = function ($request, $handler) {
    try {
        $response = $handler->handle($request);
    } catch (UnexpectedValueException $exception) {
        return (new Slim\Psr7\Response())->withStatus(404);
    } catch (HttpNotFoundException $exception) {
        return (new Slim\Psr7\Response())->withStatus(404);
    } catch (Throwable $exception) {
        $response = new Slim\Psr7\Response();
        $response->getBody()->write('-1');
        return $response->withStatus(500);
    }
    return $response;
};

$app->add($afterMiddleware);
$app->run();
