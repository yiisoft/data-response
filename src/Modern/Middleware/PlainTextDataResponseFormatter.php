<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Stringable;
use Yiisoft\DataResponse\Modern\DataResponse;
use Yiisoft\Http\Header;

use function is_scalar;
use function sprintf;

/**
 * Formats DataResponse as plain text.
 */
final class PlainTextDataResponseFormatter implements MiddlewareInterface
{
    /**
     * @param string $contentType The Content-Type header for the response.
     * @param string $encoding The encoding for the Content-Type header.
     */
    public function __construct(
        private readonly string $contentType = 'text/plain',
        private readonly string $encoding = 'UTF-8',
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (!$response instanceof DataResponse) {
            return $response;
        }

        $data = $response->data;
        $response = $response->getResponse();

        if (!is_scalar($data) && $data !== null && !$data instanceof Stringable) {
            throw new LogicException(sprintf(
                'Data must be either a scalar value, null, or a stringable object. %s given.',
                get_debug_type($data),
            ));
        }

        if (!empty($data)) {
            $response
                ->getBody()
                ->write((string) $data);
        }

        return $response->withHeader(Header::CONTENT_TYPE, "$this->contentType; charset=$this->encoding");
    }
}
