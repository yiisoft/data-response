<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Formatter;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\Modern\ResponseFormatterInterface;
use Yiisoft\Http\Header;
use Yiisoft\Json\Json;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * `JsonResponseFormatter` formats the response data as JSON.
 */
final class JsonResponseFormatter implements ResponseFormatterInterface
{
    /**
     * @param string $contentType The Content-Type header for the response.
     * @param string $encoding The encoding for the Content-Type header.
     * @param int $options The encoding options. For more details please refer to
     * {@link https://www.php.net/manual/en/function.json-encode.php}.
     */
    public function __construct(
        private readonly string $contentType = 'application/json',
        private readonly string $encoding = 'UTF-8',
        private readonly int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws JsonException
     */
    public function format(mixed $data, ResponseInterface $response): ResponseInterface
    {
        if ($data !== null) {
            $response
                ->getBody()
                ->write(Json::encode($data, $this->options));
        }

        return $response->withHeader(Header::CONTENT_TYPE, "$this->contentType; charset=$this->encoding");
    }
}
