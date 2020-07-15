<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse;

use Yiisoft\Http\Status;

interface DataResponseFactoryInterface
{
    public function createResponse($data = null, int $code = Status::OK, string $reasonPhrase = ''): DataResponse;
}
