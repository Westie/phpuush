<?php

namespace App\Router;

use App\Repository\File as FileRepository;
use App\Repository\User as UserRepository;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Psr7\Stream;

class Api
{
    private $container;
    private $fileRepository;
    private $userRepository;

    /**
     *  Called when connecting an app to this controller provider
     */
    public function __invoke(RouteCollectorProxyInterface $app)
    {
        $app->post('/auth', [ $this, 'auth' ]);
        $app->post('/up', [ $this, 'up' ]);
        $app->any('/del', [ $this, 'del' ]);
        $app->any('/hist', [ $this, 'hist' ]);
        $app->any('/thumb', [ $this, 'thumb' ]);

        $this->container = $app->getContainer();

        $this->userRepository = $this->container->get(UserRepository::class);
        $this->fileRepository = $this->container->get(FileRepository::class);
    }

    /**
     *  Authentication
     *
     *   - Request:
     *      + e = email address
     *      + p = password
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
        $post = $request->getParsedBody();
        $user = $this->userRepository->getUserByCredentials($post['e'], $post['p']);

        // write some weird data
        $response->getBody()->write(implode(',', [
            1,
            $user['api_key'],
            '',
            $this->fileRepository->getFileSizeForUser($user['rowid']),
        ]) . PHP_EOL);

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
     *      > 1,{http://pointer/url},{id},{size}
     *
     *   - Response (failure):
     *      > -1
     */
    public function up(Request $request, Response $response, array $arguments): Response
    {
        $post = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        $user = $this->userRepository->getUserByKey($post['k']);

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
        $file = $this->fileRepository->createFile([
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
            1,
            $file['file_url'],
            $file['rowid'],
            $file['file_size'],
        ]) . PHP_EOL);

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
        $post = $request->getParsedBody();

        $user = $this->userRepository->getUserByKey($post['k']);
        $file = $this->fileRepository->getFileById((int) $post['i']);

        if ((int) $file['users_id'] !== (int) $user['rowid']) {
            throw new Exception();
        }

        // delete file
        $this->fileRepository->deleteFile((int) $file['rowid']);

        // retrieve history
        $response->getBody()->write('0' . PHP_EOL);

        foreach ($this->fileRepository->getFilesForUser($user['rowid']) as $file) {
            $response->getBody()->write(implode(',', [
                $file['rowid'],
                date('Y-m-d H:i:s', $file['timestamp']),
                $file['file_url'],
                $file['file_name'],
                $file['views'],
                1,
            ]) . PHP_EOL);
        }

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
        $post = $request->getParsedBody();
        $user = $this->userRepository->getUserByKey($post['k']);

        // retrieve history
        $response->getBody()->write('0' . PHP_EOL);

        foreach ($this->fileRepository->getFilesForUser($user['rowid']) as $file) {
            $response->getBody()->write(implode(',', [
                $file['rowid'],
                date('Y-m-d H:i:s', $file['timestamp']),
                $file['file_url'],
                $file['file_name'],
                $file['views'],
                1,
            ]) . PHP_EOL);
        }

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
        $post = $request->getParsedBody();

        $user = $this->userRepository->getUserByKey($post['k']);
        $file = $this->fileRepository->getFileById((int) $post['i']);

        if ((int) $file['users_id'] !== (int) $user['rowid']) {
            throw new Exception();
        }

        $image = null;
        $sourceWidth = null;
        $sourceHeight = null;
        $sourceFileType = null;
        $imageWidth = null;
        $imageHeight = null;

        // figure out file
        list($sourceWidth, $sourceHeight, $sourceFileType) = getimagesize($file['file_path']);
        list($imageWidth, $imageHeight) = getimagesize($file['file_path']);

        if ($sourceFileType === IMAGETYPE_GIF) {
            $image = imagecreatefromgif($file['file_path']);
            $transparency = imagecolorallocate($image, 255, 255, 255);
            imagecolortransparent($image, $transparency);
        } elseif ($sourceFileType === IMAGETYPE_JPEG) {
            $image = imagecreatefromjpeg($file['file_path']);
        } elseif ($sourceFileType === IMAGETYPE_PNG) {
            $image = imagecreatefrompng($file['file_path']);
        } else {
            throw new Exception();
        }

        // create thumbnail
        $thumbBoundaries = [
            'width' => 100,
            'height' => 100,
        ];

        if ($sourceWidth > $thumbBoundaries['width'] || $sourceHeight > $thumbBoundaries['height']) {
            $scaler = 1;

            if ($sourceWidth > $sourceHeight) {
                $scaler = $thumbBoundaries['width'] / $sourceWidth;
            } elseif ($sourceWidth < $sourceHeight) {
                $scaler = $thumbBoundaries['height'] / $sourceHeight;
            }

            $sourceWidth *= $scaler;
            $sourceHeight *= $scaler;
        }

        $destination = imagecreatetruecolor($sourceWidth, $sourceHeight);

        imagecopyresampled($destination, $image, 0, 0, 0, 0, $sourceWidth, $sourceHeight, $imageWidth, $imageHeight);

        // save it to file, somewhere
        $buffer = stream_get_meta_data(tmpfile())['uri'];

        if ($sourceFileType === IMAGETYPE_GIF) {
            imagegif($destination, $buffer);
        } elseif ($sourceFileType === IMAGETYPE_JPEG) {
            imagejpeg($destination, $buffer);
        } elseif ($sourceFileType === IMAGETYPE_PNG) {
            imagepng($destination, $buffer);
        }

        $fp = fopen($buffer, 'r');

        return $response->withStatus(200)
            ->withHeader('Cache-Control', 'public')
            ->withHeader('Content-Disposition', 'inline')
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Content-Type', $file['mime_type'])
            ->withBody(new Stream($fp));
    }
}
