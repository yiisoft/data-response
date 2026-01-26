<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\DataResponseFormatter;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\Modern\DataStream\DataStream;
use Yiisoft\DataResponse\Modern\DataStream\Formatter\XmlDataFormatter;
use Yiisoft\Http\Header;

final class XmlDataResponseFormatter implements DataResponseFormatterInterface
{
    public function __construct(
        private readonly XmlDataFormatter $formatter = new XmlDataFormatter(),
        private readonly string $contentType = 'application/xml',
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
