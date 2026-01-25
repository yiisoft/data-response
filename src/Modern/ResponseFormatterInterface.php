<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern;

use Psr\Http\Message\ResponseInterface;

/**
 * ResponseFormatterInterface is the interface that should be implemented by data response formatters.
 */
interface ResponseFormatterInterface
{
    /**
     * Returns an instance of the response with formatted response data.
     *
     * @param mixed $data The data to format.
     * @param ResponseInterface $response The response instance.
     *
     * @return ResponseInterface The response with formatted response data.
     */
    public function format(mixed $data, ResponseInterface $response): ResponseInterface;
}
