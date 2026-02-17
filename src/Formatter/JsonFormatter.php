<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Header;
use Yiisoft\Json\Json;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * Formatter that encodes data as JSON and sets appropriate response headers.
 */
final class JsonFormatter implements FormatterInterface
{
    /**
     * @param string $contentType The content type for the response.
     * @param string $encoding The character encoding for the response.
     * @param int $options The JSON encoding options.
     */
    public function __construct(
        private readonly string $contentType = 'application/json',
        private readonly string $encoding = 'UTF-8',
        private readonly int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    ) {}

    public function formatData(mixed $data): string
    {
        try {
            return Json::encode($data, $this->options);
        } catch (JsonException $e) {
            throw new DataEncodingException($e->getMessage(), previous: $e);
        }
    }

    public function formatResponse(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader(Header::CONTENT_TYPE, "$this->contentType; charset=$this->encoding");
    }
}
