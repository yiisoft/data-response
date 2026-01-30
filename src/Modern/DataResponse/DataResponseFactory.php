<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\DataResponse;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\Modern\DataStream\DataStream;
use Yiisoft\DataResponse\Modern\Formatter\FormatterInterface;
use Yiisoft\DataResponse\Modern\Formatter\PlainTextFormatter;
use Yiisoft\Http\Status;

final class DataResponseFactory implements DataResponseFactoryInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ?FormatterInterface $formatter = null,
        private readonly FormatterInterface $fallbackFormatter = new PlainTextFormatter(),
    ) {}

    public function createResponse(
        mixed $data = null,
        int $code = Status::OK,
        string $reasonPhrase = '',
    ): ResponseInterface {
        $body = new DataStream($data, $this->formatter, $this->fallbackFormatter);
        return $this->responseFactory
            ->createResponse($code, $reasonPhrase)
            ->withBody($body);
    }
}
