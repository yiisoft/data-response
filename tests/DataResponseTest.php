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
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;

class DataResponseTest extends TestCase
{
    public function testCreateResponse(): void
    {
        $dataResponse = $this->createFactory()->createResponse('test');
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
        $dataResponse = $this->createFactory()->createResponse('test');
        $data = $dataResponse->getData();
        $data .= '-changed';
        $dataResponse = $dataResponse->withData($data);
        $dataResponse->getBody()->rewind();

        $this->assertSame('test-changed', $dataResponse->getBody()->getContents());
    }

    public function testChangeResponseDataWithFormatter(): void
    {
        $dataResponse = $this->createFactory()->createResponse('test-value');
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse = $dataResponse->withData('new-test-value');
        $dataResponse->getBody()->rewind();

        $this->assertSame('"new-test-value"', $dataResponse->getBody()->getContents());
    }

    public function testSetResponseFormatter(): void
    {
        $dataResponse = $this->createFactory()->createResponse('test');
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse->getBody()->rewind();

        $this->assertTrue($dataResponse->hasResponseFormatter());
        $this->assertSame('"test"', $dataResponse->getBody()->getContents());
        $this->assertSame(['application/json'], $dataResponse->getHeader('Content-Type'));
    }

    public function testSetEmptyResponseFormatter(): void
    {
        $dataResponse = $this->createFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse->getBody()->rewind();

        $this->assertTrue($dataResponse->hasResponseFormatter());
        $this->assertSame('', $dataResponse->getBody()->getContents());
        $this->assertSame(['application/json'], $dataResponse->getHeader('Content-Type'));
    }

    public function testSetResponseLoopFormatter(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DataResponseFormatterInterface should not return instance of DataResponse.');

        $dataResponse = new DataResponse('test', Status::OK, '', new Psr17Factory());
        $dataResponse = $dataResponse->withResponseFormatter(new LoopDataResponseFormatter());
        $dataResponse->getBody()->rewind();
    }

    public function testSetEmptyDataWithoutFormatter(): void
    {
        $dataResponse = $this->createFactory()->createResponse();
        $dataResponse->getBody()->rewind();

        $this->assertSame('', $dataResponse->getBody()->getContents());
        $this->assertEmpty($dataResponse->getHeaders());
    }

    public function testSetNotStringData(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Data must be a string value.');
        $factory = new Psr17Factory();
        $dataResponse = new DataResponse(100, Status::OK, '', $factory);
        $dataResponse->getBody()->rewind();
    }

    public function testGetHeader(): void
    {
        $dataResponse = $this->createFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());

        $this->assertEquals(['application/json'], $dataResponse->getHeader(Header::CONTENT_TYPE));
    }

    public function testGetHeaderLine(): void
    {
        $dataResponse = $this->createFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());

        $this->assertEquals('application/json', $dataResponse->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testGetHeaders(): void
    {
        $dataResponse = $this->createFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());

        $this->assertEquals([Header::CONTENT_TYPE => ['application/json']], $dataResponse->getHeaders());
    }

    public function testHasHeader(): void
    {
        $dataResponse = $this->createFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());

        $this->assertTrue($dataResponse->hasHeader(Header::CONTENT_TYPE));
        $this->assertFalse($dataResponse->hasHeader(Header::ACCEPT_LANGUAGE));
    }

    public function testWithoutHeader(): void
    {
        $dataResponse = $this->createFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse = $dataResponse->withoutHeader(Header::CONTENT_TYPE);
        $this->assertFalse($dataResponse->hasHeader(Header::CONTENT_TYPE));
    }

    public function testWithHeader(): void
    {
        $dataResponse = $this->createFactory()->createResponse()
            ->withHeader(Header::CONTENT_TYPE, 'application/json');

        $this->assertEquals(['Content-Type' => ['application/json']], $dataResponse->getHeaders());
    }

    public function testWithAddedHeader(): void
    {
        $dataResponse = $this->createFactory()->createResponse()
            ->withHeader(Header::CONTENT_TYPE, 'application/json')
            ->withAddedHeader(Header::CONTENT_TYPE, 'application/xml');

        $this->assertEquals(['Content-Type' => ['application/json', 'application/xml']], $dataResponse->getHeaders());
    }

    public function testGetStatusCode(): void
    {
        $factory = $this->createFactory();

        $this->assertEquals(200, $factory->createResponse()->getStatusCode());
        $this->assertEquals(400, $factory->createResponse(null, Status::BAD_REQUEST)->getStatusCode());
    }

    public function testWithStatus(): void
    {
        $dataResponse = $this->createFactory()->createResponse()->withStatus(Status::BAD_REQUEST, 'reason');

        $this->assertEquals(400, $dataResponse->getStatusCode());
        $this->assertEquals('reason', $dataResponse->getReasonPhrase());
    }

    public function testGetProtocolVersion(): void
    {
        $this->assertEquals('1.1', $this->createFactory()->createResponse()->getProtocolVersion());
    }

    public function testWithProtocolVersion(): void
    {
        $dataResponse = $this->createFactory()->createResponse()->withProtocolVersion('1.0');
        $this->assertEquals('1.0', $dataResponse->getProtocolVersion());
    }

    public function testWithBody(): void
    {
        $dataResponse = $this->createFactory()->createResponse()
            ->withBody(Stream::create('test'));

        $dataResponse->getBody()->rewind();
        $this->assertEquals('test', $dataResponse->getBody()->getContents());
    }

    public function testGetData(): void
    {
        $dataResponse = $this->createFactory()->createResponse('test');
        $this->assertEquals('test', $dataResponse->getData());

        $dataResponse = $this->createFactory()->createResponse(fn() => 'test2');
        $this->assertEquals('test2', $dataResponse->getData());
    }

    public function testHasData(): void
    {
        $dataResponse = $this->createFactory()->createResponse('test');
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse->getBody()->rewind();

        $this->assertTrue($dataResponse->hasData());
    }

    public function testHasDataWithEmptyData(): void
    {
        $dataResponse = $this->createFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse->getBody()->rewind();

        $this->assertFalse($dataResponse->hasData());
    }

    private function createFactory(): DataResponseFactory
    {
        return new DataResponseFactory(new Psr17Factory());
    }
}
