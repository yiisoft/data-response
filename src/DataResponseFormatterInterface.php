<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse;

use Psr\Http\Message\ResponseInterface;

interface DataResponseFormatterInterface
{
    public function format(DataResponse $dataResponse): ResponseInterface;
}
