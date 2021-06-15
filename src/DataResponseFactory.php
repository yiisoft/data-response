<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Http\Status;

/**
 * DataResponseFactory creates an instance of the data response {@see DataResponse}.
 */
final class DataResponseFactory implements DataResponseFactoryInterface
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function createResponse($data = null, int $code = Status::OK, string $reasonPhrase = ''): DataResponse
    {
        return new DataResponse($data, $code, $reasonPhrase, $this->responseFactory);
    }
}
