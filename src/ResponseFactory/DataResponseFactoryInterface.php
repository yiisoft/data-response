<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Status;
use Yiisoft\DataResponse\DataStream\DataStream;

/**
 * Interface for factories that create HTTP responses with {@see DataStream} body.
 */
interface DataResponseFactoryInterface
{
    /**
     * Creates an HTTP response with the given data wrapped in a {@see DataStream}.
     *
     * @param mixed $data The data to include in the response body.
     * @param int $code The HTTP status code.
     * @param string $reasonPhrase The reason phrase. If empty, a default phrase for the status code will be used.
     *
     * @return ResponseInterface The created response with {@see DataStream} body.
     */
    public function createResponse(
        mixed $data = null,
        int $code = Status::OK,
        string $reasonPhrase = '',
    ): ResponseInterface;
}
