<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\Modern\DataResponseFormatter\HtmlDataResponseFormatter;
use Yiisoft\DataResponse\Modern\DataStream\DataStream;

final class HtmlDataResponseMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly HtmlDataResponseFormatter $formatter = new HtmlDataResponseFormatter(),
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
