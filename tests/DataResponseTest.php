<?php

namespace Yiisoft\DataResponse\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\Tests\Stub\LoopDataResponseFormatter;
use Yiisoft\Http\Status;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\DataResponse;

class DataResponseTest extends TestCase
{
    public function testCreateResponse(): void
    {
        $factory = new Psr17Factory();
        $dataResponse = new DataResponse('test', Status::OK, '', $factory);
        $dataResponse = $dataResponse->withHeader('Content-Type', 'application/json');
        $dataResponse->getBody()->rewind();

        $this->assertInstanceOf(ResponseInterface::class, $dataResponse);
        $this->assertSame(['application/json'], $dataResponse->getResponse()->getHeader('Content-Type'));
        $this->assertSame(['application/json'], $dataResponse->getHeader('Content-Type'));
        $this->assertSame($dataResponse->getResponse()->getBody(), $dataResponse->getBody());
        $this->assertSame('test', $dataResponse->getBody()->getContents());
    }

    public function testChangeResponseData(): void
    {
        $factory = new Psr17Factory();
        $dataResponse = new DataResponse('test', Status::OK, '', $factory);
        $data = $dataResponse->getData();
        $data .= '-changed';
        $dataResponse = $dataResponse->withData($data);
        $dataResponse->getBody()->rewind();

        $this->assertSame('test-changed', $dataResponse->getBody()->getContents());
    }

    public function testSetResponseFormatter(): void
    {
        $factory = new Psr17Factory();
        $dataResponse = new DataResponse('test', Status::OK, '', $factory);
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse->getBody()->rewind();

        $this->assertTrue($dataResponse->hasResponseFormatter());
        $this->assertSame('"test"', $dataResponse->getBody()->getContents());
        $this->assertSame(['application/json'], $dataResponse->getHeader('Content-Type'));
    }

    public function testSetEmptyResponseFormatter(): void
    {
        $factory = new Psr17Factory();
        $dataResponse = new DataResponse(null, Status::OK, '', $factory);
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse->getBody()->rewind();

        $this->assertTrue($dataResponse->hasResponseFormatter());
        $this->assertSame('null', $dataResponse->getBody()->getContents());
        $this->assertSame(['application/json'], $dataResponse->getHeader('Content-Type'));
    }

    public function testSetResponseLoopFormatter(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DataResponseFormatterInterface should not return instance of DateResponse.');

        $dataResponse = new DataResponse('test', Status::OK, '', new Psr17Factory());
        $dataResponse = $dataResponse->withResponseFormatter(new LoopDataResponseFormatter());
        $dataResponse->getBody()->rewind();
    }
}
