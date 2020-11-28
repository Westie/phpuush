<?php

namespace App\Router;

use App\Repository\File as FileRepository;
use App\Repository\User as UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteCollectorProxyInterface;

class Api
{
    private $container;

    /**
     *  Called when connecting an app to this controller provider
     */
    public function __invoke(RouteCollectorProxyInterface $app)
    {
        $app->any('/auth', [ $this, 'auth' ]);
        $app->any('/up', [ $this, 'up' ]);
        $app->any('/del', [ $this, 'del' ]);
        $app->any('/hist', [ $this, 'hist' ]);
        $app->any('/thumb', [ $this, 'thumb' ]);

        $this->container = $app->getContainer();
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
        $post = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $user = $this->container->get(UserRepository::class)->getUserByKey($post['k']);

        if (!file_exists($user['storage_path'])) {
            mkdir($user['storage_path'], 0777, true);
        }

        if (empty($files['f'])) {
            throw new Exception();
        }

        // move file to somewhere locally
        $fileName = uniqid();
        $filePath = $user['storage_path'] . '/' . $fileName;

        $files['f']->moveTo($filePath);

        // add our file
        $file = $this->container->get(FileRepository::class)->createFile([
            'users_id' => $user['rowid'],
            'file_name' => $files['f']->getClientFilename(),
            'file_location' => $user['storage_folder'] . '/' . $fileName,
            'file_size' => filesize($filePath),
            'file_hash' => md5_file($filePath),
            'mime_type' => $files['f']->getClientMediaType(),
            'timestamp' => time(),
            'ip_address' => null,
        ]);

        // output feed
        $response->getBody()->write(implode(',', [
            0,
            $file['file_url'],
            $file['rowid'],
            $file['file_size'],
        ]));

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
