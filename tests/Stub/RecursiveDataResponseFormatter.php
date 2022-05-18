<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Stub;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;

final class RecursiveDataResponseFormatter implements DataResponseFormatterInterface
{
    public function format(DataResponse $dataResponse): ResponseInterface
    {
        $dataResponse
            ->getBody()
            ->getContents();

        return $dataResponse->getResponse();
    }
}
