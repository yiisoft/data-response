<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\FormatterInterface;

/**
 * Abstract middleware class that applies a formatter to {@see DataStream} responses
 * and sets appropriate response headers.
 *
 * The middleware only formats responses whose body is a {@see DataStream} without a formatter set.
 */
abstract class AbstractDataResponseMiddleware implements MiddlewareInterface
{
    /**
     * @param FormatterInterface $formatter The formatter to apply to the response.
     */
    public function __construct(
        private readonly FormatterInterface $formatter,
    ) {}

    final public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $body = $response->getBody();
        if (!$body instanceof DataStream || $body->hasFormatter()) {
            return $response;
        }

        $body->changeFormatter($this->formatter);
        return $this->formatter->formatResponse($response);
    }
}
