<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\DataResponseFormatter;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\Modern\DataStream\DataStream;
use Yiisoft\DataResponse\Modern\DataStream\Formatter\JsonDataFormatter;
use Yiisoft\Http\Header;

final class JsonDataResponseFormatter implements DataResponseFormatterInterface
{
    public function __construct(
        private readonly JsonDataFormatter $formatter = new JsonDataFormatter(),
        private readonly string $contentType = 'application/json',
        private readonly string $encoding = 'UTF-8',
    ) {}

    public function format(DataStream $body, ResponseInterface $response): ResponseInterface
    {
        $body->changeFormatter($this->formatter);

        return $response
            ->withHeader(Header::CONTENT_TYPE, "$this->contentType; charset=$this->encoding")
            ->withBody($body);
    }
}
