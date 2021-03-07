<?php

namespace App\Router;

use App\Configuration\Configuration;
use App\Repository\File as FileRepository;
use App\Router\Traits\Expiration;
use DateTime;
use DomainException;
use Exception;
use Highlight\Highlighter;
use HighlightUtilities;
use Parsedown;
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
        $fp = null;

        $ext = strtolower($args['ext']);

        if ($ext === 'md') {
            $fp = $this->usingMarkdown($code);
        } else {
            $fp = $this->usingHighlighter($code, $ext);
        }

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
     *  Turn code to markup
     */
    private function usingHighlighter(string $code, string $ext)
    {
        $fp = fopen('php://temp', 'rw');

        try {
            // get theme
            $theme = $this->container->get(Configuration::class)->get('files.theme');

            if (empty($theme)) {
                $theme = 'vs';
            }

            $stylesheet = HighlightUtilities\getStyleSheet($theme);

            // output markup
            $highlighted = (new Highlighter())->highlight($ext, $code);

            fwrite($fp, '<body style="margin: 0">');
            fwrite($fp, '<style>' . $stylesheet . '</style>');
            fwrite($fp, '<pre><code class="hljs ' . $highlighted->language . '">');
            fwrite($fp, $highlighted->value);
            fwrite($fp, '</code></pre>');
            fwrite($fp, '</body>');
        } catch (DomainException $e) {
            fwrite($fp, '<pre><code>');
            fwrite($fp, htmlentities($code));
            fwrite($fp, '</code></pre>');
        }

        rewind($fp);

        return $fp;
    }

    /**
     *  Turn markdown to HTML
     */
    private function usingMarkdown(string $code)
    {
        $fp = fopen('php://temp', 'rw');

        try {
            $parsedown = new Parsedown();

            $stylesheet = file_get_contents(APP_DIR . 'templates/github-markdown.css');
            $stylesheet = str_replace("\n", '', $stylesheet);

            fwrite($fp, '<body style="max-width: 1000px; margin: auto">');
            fwrite($fp, '<style>' . $stylesheet . '</style>');
            fwrite($fp, '<article class="markdown-body">');
            fwrite($fp, $parsedown->text($code));
            fwrite($fp, '</article>');
            fwrite($fp, '</body>');
        } catch (Exception $e) {
            fwrite($fp, '<pre><code>');
            fwrite($fp, htmlentities($code));
            fwrite($fp, '</code></pre>');
        }

        rewind($fp);

        return $fp;
    }
}
