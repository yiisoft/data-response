<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\HasContentType;
use Yiisoft\Http\Header;
use Yiisoft\Serializer\JsonSerializer;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;

final class JsonDataResponseFormatter implements DataResponseFormatterInterface
{
    use HasContentType;
    /**
     * @var string the Content-Type header for the response
     */
    private string $contentType = 'application/json';

    private int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public function format(DataResponse $dataResponse): ResponseInterface
    {
        $jsonSerializer = new JsonSerializer($this->options);
        $content = $jsonSerializer->serialize($dataResponse->getData());
        $response = $dataResponse->getResponse();
        $response->getBody()->write($content);

        return $response->withHeader(Header::CONTENT_TYPE, $this->contentType);
    }

    public function withOptions(int $options): self
    {
        $formatter = clone $this;
        $formatter->options = $options;
        return $formatter;
    }
}
