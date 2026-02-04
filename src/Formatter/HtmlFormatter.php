<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use Psr\Http\Message\ResponseInterface;
use Stringable;
use Yiisoft\Http\Header;

use function is_scalar;
use function sprintf;

/**
 * Formatter that converts data to HTML and sets appropriate response headers.
 *
 * Supports scalar values, null, and objects implementing {@see Stringable}.
 */
final class HtmlFormatter implements FormatterInterface
{
    /**
     * @param string $contentType The content type for the response.
     * @param string $encoding The character encoding for the response.
     */
    public function __construct(
        private readonly string $contentType = 'text/html',
        private readonly string $encoding = 'UTF-8',
    ) {}

    public function formatData(mixed $data): string
    {
        if ($data === null) {
            return '';
        }

        if (!is_scalar($data) && !$data instanceof Stringable) {
            throw new DataEncodingException(
                sprintf(
                    'Data must be either a scalar value, null, or a stringable object. %s given.',
                    get_debug_type($data),
                ),
            );
        }

        return (string) $data;
    }

    public function formatResponse(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader(Header::CONTENT_TYPE, "$this->contentType; charset=$this->encoding");
    }
}
