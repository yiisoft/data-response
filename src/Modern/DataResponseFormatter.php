<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\Middleware\DataResponseFormatterInterface;

final class DataResponseFormatter implements MiddlewareInterface
{
    public function __construct(
        private readonly DataResponseFormatterInterface $defaultFormatter,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response instanceof DataResponse) {
            $response = ($response->responseFormatter ?? $this->defaultFormatter)->format($response);
        }
        return $response;
    }
}
