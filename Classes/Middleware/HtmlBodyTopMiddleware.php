<?php
declare(strict_types=1);

namespace HauerHeinrich\HhSeo\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Routing\PageArguments;

class HtmlBodyTopMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ConnectionPool $connectionPool) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);


        if (!str_contains($response->getHeaderLine('Content-Type'), 'text/html')) {
            return $response;
        }

        // $request already has routing set by outer middlewares before ours runs
        $htmlBodyTop = $this->resolveHtmlBodyTop($request);
        if (empty($htmlBodyTop)) {
            return $response;
        }


        $body = (string)$response->getBody();

        if (!preg_match('/<body[^>]*>/i', $body, $matches)) {
            return $response;
        }

        $bodyTag = $matches[0];
        $insertPos = strpos($body, $bodyTag);
        if ($insertPos === false) {
            return $response;
        }

        $modifiedBody = substr($body, 0, $insertPos + strlen($bodyTag))
            . $htmlBodyTop
            . substr($body, $insertPos + strlen($bodyTag));

        $stream = new Stream('php://temp', 'rw');
        $stream->write($modifiedBody);

        return $response->withBody($stream);
    }

    private function resolveHtmlBodyTop(ServerRequestInterface $request): string
    {
        $routing = $request->getAttribute('routing');
        if (!$routing instanceof PageArguments) {
            return '';
        }

        // Walk up the page tree (slide) until html_body_top is non-empty
        $uid = $routing->getPageId();
        while ($uid > 0) {
            $row = $this->connectionPool
                ->getConnectionForTable('pages')
                ->select(['uid', 'pid', 'html_body_top'], 'pages', ['uid' => $uid])
                ->fetchAssociative();

            if ($row === false) {
                break;
            }

            if (!empty(trim((string)($row['html_body_top'] ?? '')))) {
                return $row['html_body_top'];
            }

            $uid = (int)$row['pid'];
        }

        return '';
    }
}
