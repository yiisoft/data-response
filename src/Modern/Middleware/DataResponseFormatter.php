<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\Modern\DataResponse;
use Yiisoft\DataResponse\Modern\ResponseFormatterInterface;

class DataResponseFormatter implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFormatterInterface $defaultFormatter,
    ) {}

    final public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response instanceof DataResponse) {
            $response = $this->defaultFormatter->format($response->data, $response->getResponse());
        }
        return $response;
    }
}
