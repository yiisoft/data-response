<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Stringable;
use Yiisoft\DataResponse\Modern\DataResponse\Formatter\PlainTextDataResponseFormatter;
use Yiisoft\DataResponse\Modern\DataStream\DataStream;
use Yiisoft\Http\Status;

final class PlainTextResponseFactory
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly PlainTextDataResponseFormatter $formatter,
    ) {}

    public function createResponse(
        string|bool|int|float|Stringable|null $data = null,
        int $code = Status::OK,
        string $reasonPhrase = '',
    ): ResponseInterface {
        return $this->formatter->format(
            new DataStream($data),
            $this->responseFactory->createResponse($code, $reasonPhrase),
        );
    }
}
