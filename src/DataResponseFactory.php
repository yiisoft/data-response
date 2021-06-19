<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Yiisoft\Http\Status;

/**
 * DataResponseFactory creates an instance of the data response {@see DataResponse}.
 */
final class DataResponseFactory implements DataResponseFactoryInterface
{
    private ResponseFactoryInterface $responseFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
    }

    public function createResponse($data = null, int $code = Status::OK, string $reasonPhrase = ''): DataResponse
    {
        return new DataResponse($data, $code, $reasonPhrase, $this->responseFactory, $this->streamFactory);
    }
}
