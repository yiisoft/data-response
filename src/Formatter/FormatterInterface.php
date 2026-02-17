<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Interface that should be implemented by data formatters.
 *
 * Data formatters are responsible for converting data into a specific format (e.g., JSON, XML, HTML)
 * and applying the formatted content to HTTP responses.
 */
interface FormatterInterface
{
    /**
     * Formats the given data into a stream or string representation.
     *
     * @param mixed $data The data to be formatted.
     *
     * @throws DataEncodingException If the data cannot be encoded into the target format.
     *
     * @return StreamInterface|string The formatted data as a stream or string.
     */
    public function formatData(mixed $data): StreamInterface|string;

    /**
     * Applies the formatter's headers to the response.
     *
     * This method does not format the response body data. Use {@see formatData()} to format the data.
     *
     * @param ResponseInterface $response The response to apply headers to.
     *
     * @return ResponseInterface The response with the applied headers.
     */
    public function formatResponse(ResponseInterface $response): ResponseInterface;
}
