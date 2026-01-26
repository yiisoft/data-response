<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Formatter;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Header;
use Yiisoft\Json\Json;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

final class JsonFormatter implements FormatterInterface
{
    public function __construct(
        private readonly string $contentType = 'application/json',
        private readonly string $encoding = 'UTF-8',
        private readonly int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    ) {}

    /**
     * @throws JsonException
     */
    public function formatData(mixed $data): string
    {
        if ($data === null) {
            return '';
        }

        return Json::encode($data, $this->options);
    }

    public function formatResponse(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader(Header::CONTENT_TYPE, "$this->contentType; charset=$this->encoding");
    }
}
