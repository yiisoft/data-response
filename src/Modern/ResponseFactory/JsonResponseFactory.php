<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\Modern\DataResponseFormatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\Modern\DataStream\DataStream;
use Yiisoft\Http\Status;

final class JsonResponseFactory
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly JsonDataResponseFormatter $formatter,
    ) {
    }

    public function createResponse(
        mixed $data = null,
        int $code = Status::OK,
        string $reasonPhrase = '',
    ): ResponseInterface {
        return $this->formatter->format(
            new DataStream($data),
            $this->responseFactory->createResponse($code, $reasonPhrase),
        );
    }
}
