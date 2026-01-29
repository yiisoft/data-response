<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\Modern\DataStream\DataStream;
use Yiisoft\DataResponse\Modern\Formatter\FormatterInterface;

abstract class AbstractDataResponseMiddleware implements MiddlewareInterface
{
    protected readonly FormatterInterface $formatter;

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
