<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\Modern\DataResponseFormatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\Modern\DataStream\DataStream;

final class JsonDataResponseMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly JsonDataResponseFormatter $formatter = new JsonDataResponseFormatter(),
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $body = $response->getBody();
        return $body instanceof DataStream
            ? $this->formatter->format($body, $response)
            : $response;
    }
}
