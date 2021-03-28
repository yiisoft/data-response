<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\Tests\Stub\LoopDataResponseFormatter;
use Yiisoft\DataResponse\Tests\Stub\RecursiveDataResponseFormatter;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;

class DataResponseTest extends TestCase
{
    public function testSetResponseRecursiveFormatter(): void
    {
        $dataResponse = new DataResponse('test', Status::OK, '', new Psr17Factory());
        $dataResponse = $dataResponse->withResponseFormatter(new RecursiveDataResponseFormatter());
        $dataResponse->getBody()->rewind();
    }

    private function createFactory(): DataResponseFactory
    {
        return new DataResponseFactory(new Psr17Factory());
    }
}
