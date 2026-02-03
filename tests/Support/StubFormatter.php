<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Support;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Yiisoft\DataResponse\Formatter\FormatterInterface;

final class StubFormatter implements FormatterInterface
{
    public function __construct(
        private readonly StreamInterface|string $formattedData = '',
    ) {
    }

    public function formatData(mixed $data): StreamInterface
    {
        return $this->formattedData;
    }

    public function formatResponse(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
}
