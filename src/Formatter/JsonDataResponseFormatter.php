<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\ResponseContentTrait;
use Yiisoft\Json\Json;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * `JsonDataResponseFormatter` formats the response data as JSON.
 */
final class JsonDataResponseFormatter implements DataResponseFormatterInterface
{
    use ResponseContentTrait;

    /**
     * @var string The Content-Type header for the response.
     */
    private string $contentType = 'application/json';

    /**
     * @var string The encoding for the Content-Type header.
     */
    private string $encoding = 'UTF-8';

    /**
     * @var int The encoding options.
     */
    private int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    /**
     * @inheritDoc
     *
     * @throws JsonException
     */
    public function format(DataResponse $dataResponse): ResponseInterface
    {
        if (!$dataResponse->hasData()) {
            return $this->addToResponse($dataResponse->getResponse());
        }

        return $this->addToResponse(
            $dataResponse->getResponse(),
            Json::encode($dataResponse->getData(), $this->options),
        );
    }

    /**
     * Returns a new instance with the specified encoding options.
     *
     * @param int $options The encoding options. For more details please refer to
     * {@link https://www.php.net/manual/en/function.json-encode.php}.
     */
    public function withOptions(int $options): self
    {
        $new = clone $this;
        $new->options = $options;
        return $new;
    }
}
