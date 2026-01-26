<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\DataResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\Modern\DataStream\DataStream;
use Yiisoft\Http\Status;

final class DataResponseFactory implements DataResponseFactoryInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

    public function createResponse(
        mixed $data = null,
        int $code = Status::OK,
        string $reasonPhrase = '',
    ): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse($code, $reasonPhrase)
            ->withBody(new DataStream($data));
    }
}
