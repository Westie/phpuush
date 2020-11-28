<?php

namespace App\Router;

use App\Repository\File as FileRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Psr7\Stream;

class FileRoute
{
    private $container;

    /**
     *  Constructor
     */
    public function __construct(App $app)
    {
        $this->container = $app->getContainer();
    }

    /**
     *  Router
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        $file = $this->container->get(FileRepository::class)->getFileByAlias($args['alias']);

        if ($request->getHeaderLine('If-None-Match') === $file['file_hash']) {
            return $response->withStatus(304);
        }

        $fp = fopen($file['file_path'], 'r');

        return $response->withStatus(200)
            ->withHeader('Cache-Control', 'public')
            ->withHeader('Content-Disposition', 'inline; filename=' . json_encode($file['file_name']))
            ->withHeader('Content-Length', $file['file_size'])
            ->withHeader('Content-MD5', $file['file_hash'])
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Content-Type', $file['mime_type'])
            ->withHeader('ETag', $file['file_hash'])
            ->withBody(new Stream($fp));
    }
}
