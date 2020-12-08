<?php

namespace App\Router;

use App\Configuration\Configuration;
use App\Repository\File as FileRepository;
use App\Router\Traits\Expiration;
use DateTime;
use DomainException;
use Highlight\Highlighter;
use HighlightUtilities;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Psr7\Stream;

class FormattedFileRoute
{
    use Expiration;

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
        $fileExpiry = $this->getExpiry($file);

        if ($fileExpiry < new DateTime()) {
            return $response->withStatus(404);
        }

        $hash = md5($file['file_hash'] . $args['ext']);

        if ($request->getHeaderLine('If-None-Match') === $hash) {
            return $response->withStatus(304);
        }

        $code = file_get_contents($file['file_path']);

        $fp = fopen('php://temp', 'rw');

        try {
            $highlighted = (new Highlighter())->highlight($args['ext'], $code);

            fwrite($fp, '<body style="margin: 0">');
            fwrite($fp, '<style>' . $this->getStylesheet() . '</style>');
            fwrite($fp, '<pre><code class="hljs ' . $highlighted->language . '">');
            fwrite($fp, $highlighted->value);
            fwrite($fp, '</code></pre>');
            fwrite($fp, '</body>');
        }
        catch (DomainException $e) {
            fwrite($fp, '<pre><code>');
            fwrite($fp, htmlentities($code));
            fwrite($fp, '</code></pre>');
        }

        rewind($fp);

        return $response->withStatus(200)
            ->withHeader('Cache-Control', 'public')
            ->withHeader('Content-Disposition', 'inline; filename=' . json_encode($file['file_name']))
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Content-Type', 'text/html')
            ->withHeader('ETag', $hash)
            ->withHeader('Expires', $fileExpiry->format('D, d M Y H:i:s e'))
            ->withBody(new Stream($fp));
    }

    /**
     *  Get stylesheet
     */
    private function getStylesheet()
    {
        $theme = $this->container->get(Configuration::class)->get('files.theme');

        if (empty($theme)) {
            $theme = 'vs';
        }

        return HighlightUtilities\getStyleSheet($theme);
    }
}
