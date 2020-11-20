<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\HasContentTypeTrait;
use Yiisoft\Http\Header;
use Yiisoft\Serializer\JsonSerializer;

final class JsonDataResponseFormatter implements DataResponseFormatterInterface
{
    use HasContentTypeTrait;

    /**
     * @var string the Content-Type header for the response
     */
    private string $contentType = 'application/json';

    private int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public function format(DataResponse $dataResponse): ResponseInterface
    {
        $content = '';
        $jsonSerializer = new JsonSerializer($this->options);
        if ($dataResponse->hasData()) {
            $content = $jsonSerializer->serialize($dataResponse->getData());
        }

        $response = $dataResponse->getResponse();
        $response->getBody()->write($content);

        return $response->withHeader(Header::CONTENT_TYPE, $this->contentType);
    }

    public function withOptions(int $options): self
    {
        $new = clone $this;
        $new->options = $options;
        return $new;
    }
}
