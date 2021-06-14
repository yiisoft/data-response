<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;

/**
 * FormatDataResponse adds a formatter {@see DataResponseFormatterInterface} instance to the
 * instance of the data response {@see DataResponse}, if the formatter was not added earlier.
 */
class FormatDataResponse implements MiddlewareInterface
{
    private DataResponseFormatterInterface $responseFormatter;

    public function __construct(DataResponseFormatterInterface $responseFormatter)
    {
        $this->responseFormatter = $responseFormatter;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($response instanceof DataResponse && !$response->hasResponseFormatter()) {
            $response = $response->withResponseFormatter($this->responseFormatter);
        }

        return $response;
    }
}
