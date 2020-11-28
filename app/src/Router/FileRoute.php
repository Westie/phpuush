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
    private $app;

    /**
     *  Constructor
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     *  Router
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        $fileRepository = $this->app->getContainer()->get(FileRepository::class);

        $file = $fileRepository->getFileByAlias($args['alias']);

        return $response->withStatus(200)
            ->withHeader('Cache-Control', 'public')
            ->withHeader('Last-Modified', date('r', $file['timestamp']))
            ->withHeader('Content-Length', $file['file_size'])
            ->withHeader('Content-Type', $file['mime_type'])
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Content-MD5', $file['file_hash'])
            ->withHeader('Content-Disposition', 'inline; filename=' . json_encode($file['file_name']))
            ->withBody(new Stream($file['file_stream']));
    }
}
