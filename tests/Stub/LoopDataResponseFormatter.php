<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Stub;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;

final class LoopDataResponseFormatter implements DataResponseFormatterInterface
{
    public function format(DataResponse $dataResponse): ResponseInterface
    {
        return $dataResponse;
    }
}
