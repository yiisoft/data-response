<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

interface FormatterInterface
{
    /**
     * @throws DataEncodingException
     */
    public function formatData(mixed $data): StreamInterface|string;

    public function formatResponse(ResponseInterface $response): ResponseInterface;
}
