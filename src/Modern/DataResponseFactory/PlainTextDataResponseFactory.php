<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\DataResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\Modern\DataResponseFormatter\PlainTextDataResponseFormatter;
use Yiisoft\DataResponse\Modern\DataStream\DataStream;
use Yiisoft\Http\Status;

final class PlainTextDataResponseFactory implements DataResponseFactoryInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly PlainTextDataResponseFormatter $formatter,
    ) {}

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
