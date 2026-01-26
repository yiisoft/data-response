<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\DataResponseFormatter;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\Modern\DataStream\DataStream;

interface DataResponseFormatterInterface
{
    public function format(DataStream $body, ResponseInterface $response): ResponseInterface;
}
