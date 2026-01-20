<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Http\Status;

/**
 * `DataResponseFactory` creates an instance of the data response {@see DataResponse}.
 */
final class DataResponseFactory implements DataResponseFactoryInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

    public function createResponse($data = null, int $code = Status::OK, string $reasonPhrase = ''): DataResponse
    {
        return new DataResponse(
            $this->responseFactory->createResponse($code, $reasonPhrase),
            $data,
        );
    }
}
