<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\ResponseContentTrait;
use Yiisoft\Json\Json;

/**
 * JsonDataResponseFormatter formats the response data in JSON.
 */
final class JsonDataResponseFormatter implements DataResponseFormatterInterface
{
    use ResponseContentTrait;

    /**
     * @var string The Content-Type header for the response.
     */
    private string $contentType = 'application/json';

    /**
     * @var string The encoding to the Content-Type header.
     */
    private string $encoding = 'UTF-8';

    /**
     * @var int The encoding options.
     */
    private int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    /**
     * @inheritDoc
     *
     * @psalm-suppress MixedArgument
     */
    public function format(DataResponse $dataResponse): ResponseInterface
    {
        if ($dataResponse->hasData()) {
            $content = Json::encode($dataResponse->getData(), $this->options);
        }

        return $this->addToResponse($dataResponse->getResponse(), $content ?? null);
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
