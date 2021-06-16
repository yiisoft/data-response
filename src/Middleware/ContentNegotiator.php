<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\Http\Header;

use function get_class;
use function gettype;
use function is_object;
use function is_string;
use function sprintf;
use function strpos;

/**
 * ContentNegotiator supports response format negotiation.
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Content_negotiation
 */
final class ContentNegotiator implements MiddlewareInterface
{
    /**
     * @var array<string, DataResponseFormatterInterface>
     */
    private array $contentFormatters;

    /**
     * @param array<string, DataResponseFormatterInterface> $contentFormatters The array key is the content type,
     * and the value is an instance of {@see DataResponseFormatterInterface}.
     */
    public function __construct(array $contentFormatters)
    {
        $this->checkFormatters($contentFormatters);
        $this->contentFormatters = $contentFormatters;
    }

    /**
     * Returns a new instance with the specified content formatters.
     *
     * @param array<string, DataResponseFormatterInterface> $contentFormatters The array key is the content type,
     * and the value is an instance of {@see DataResponseFormatterInterface}.
     *
     * @return self
     */
    public function withContentFormatters(array $contentFormatters): self
    {
        $this->checkFormatters($contentFormatters);
        $new = clone $this;
        $new->contentFormatters = $contentFormatters;
        return $new;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response instanceof DataResponse && !$response->hasResponseFormatter()) {
            $accepted = $request->getHeader(Header::ACCEPT);

            foreach ($accepted as $accept) {
                foreach ($this->contentFormatters as $contentType => $formatter) {
                    if (strpos($accept, $contentType) !== false) {
                        return $response->withResponseFormatter($formatter);
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

            if (!($formatter instanceof DataResponseFormatterInterface)) {
                throw new RuntimeException(sprintf(
                    'Invalid formatter. A "%s" instance is expected, "%s" is received.',
                    DataResponseFormatterInterface::class,
                    is_object($formatter) ? get_class($formatter) : gettype($formatter),
                ));
            }
        }
    }
}
