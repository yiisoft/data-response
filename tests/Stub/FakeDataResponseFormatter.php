<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Stub;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;

final class FakeDataResponseFormatter implements DataResponseFormatterInterface
{
    private int $triggeredCount = 0;

    public function format(DataResponse $dataResponse): ResponseInterface
    {
        $this->triggeredCount++;
        return $dataResponse->getResponse();
    }

    public function getTriggeredCount(): int
    {
        return $this->triggeredCount;
    }
}
