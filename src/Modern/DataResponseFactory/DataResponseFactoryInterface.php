<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\DataResponseFactory;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Http\Status;

interface DataResponseFactoryInterface
{
    public function createResponse(
        mixed $data = null,
        int $code = Status::OK,
        string $reasonPhrase = '',
    ): ResponseInterface;
}
