<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\HasContentTypeTrait;
use Yiisoft\Http\Header;
use Yiisoft\Json\Json;

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
        if ($dataResponse->hasData()) {
            $content = Json::encode($dataResponse->getData(), $this->options);
        }

        $response = $dataResponse->getResponse();
        $response->getBody()->write($content ?? '');

        return $response->withHeader(Header::CONTENT_TYPE, $this->contentType);
    }

    /**
     * Returns a new instance with the specified encoding options.
     *
     * @param int $options The encoding options. For more details please refer to
     * {@see https://www.php.net/manual/en/function.json-encode.php}.
     *
     * @return self
     */
    public function withOptions(int $options): self
    {
        $new = clone $this;
        $new->options = $options;
        return $new;
    }
}
