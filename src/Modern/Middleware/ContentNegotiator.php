<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Yiisoft\DataResponse\Modern\DataResponse;
use Yiisoft\DataResponse\Modern\ResponseFormatterInterface;
use Yiisoft\Http\Header;

use function gettype;
use function is_string;
use function sprintf;

/**
 * ContentNegotiator supports response format negotiation.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Content_negotiation
 */
final class ContentNegotiator implements MiddlewareInterface
{
    /**
     * @param array $contentFormatters The array key is the content type, and the value is an instance of
     * {@see ResponseFormatterInterface}.
     *
     * @psalm-param array<string, ResponseFormatterInterface> $contentFormatters
     */
    public function __construct(
        private readonly array $contentFormatters,
    ) {
        $this->checkFormatters($contentFormatters);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response instanceof DataResponse) {
            $accepted = $request->getHeader(Header::ACCEPT);

            foreach ($accepted as $accept) {
                foreach ($this->contentFormatters as $contentType => $formatter) {
                    if (str_contains($accept, $contentType)) {
                        return $formatter->format($response->data, $response->getResponse());
                    }
                }
            }
        }

        return $response;
    }

    /**
     * Checks the content formatters.
     *
     * @param array $contentFormatters The content formatters to check.
     */
    private function checkFormatters(array $contentFormatters): void
    {
        foreach ($contentFormatters as $contentType => $formatter) {
            if (!is_string($contentType)) {
                throw new RuntimeException(sprintf(
                    'Invalid formatter content type. A string is expected, "%s" is received.',
                    gettype($contentType),
                ));
            }

            if (!($formatter instanceof ResponseFormatterInterface)) {
                throw new RuntimeException(sprintf(
                    'Invalid formatter. A "%s" instance is expected, "%s" is received.',
                    ResponseFormatterInterface::class,
                    get_debug_type($formatter),
                ));
            }
        }
    }
}
