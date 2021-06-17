<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use stdClass;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\Formatter\XmlDataResponseFormatter;
use Yiisoft\DataResponse\Tests\Stub\FakeDataResponseFormatter;
use Yiisoft\DataResponse\Tests\Stub\LoopDataResponseFormatter;
use Yiisoft\DataResponse\Tests\Stub\RecursiveDataResponseFormatter;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;

final class DataResponseTest extends TestCase
{
    public function testCreateResponse(): void
    {
        $dataResponse = $this->createDataResponse('test');
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
        $dataResponse = $this->createDataResponse('test');
        $data = $dataResponse->getData();
        $data .= '-changed';
        $dataResponse = $dataResponse->withData($data);
        $dataResponse->getBody()->rewind();

        $this->assertSame('test-changed', $dataResponse->getBody()->getContents());
    }

    public function testChangeResponseDataWithFormatter(): void
    {
        $dataResponse = $this->createDataResponse('test-value');
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse = $dataResponse->withData('new-test-value');
        $dataResponse->getBody()->rewind();

        $this->assertSame('"new-test-value"', $dataResponse->getBody()->getContents());
    }

    public function testSetResponseFormatter(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse->getBody()->rewind();

        $this->assertTrue($dataResponse->hasResponseFormatter());
        $this->assertSame('"test"', $dataResponse->getBody()->getContents());
        $this->assertSame(['application/json; charset=UTF-8'], $dataResponse->getHeader('Content-Type'));
    }

    public function testSetEmptyResponseFormatter(): void
    {
        $dataResponse = $this->createDataResponseFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse->getBody()->rewind();

        $this->assertTrue($dataResponse->hasResponseFormatter());
        $this->assertSame('', $dataResponse->getBody()->getContents());
        $this->assertSame(['application/json; charset=UTF-8'], $dataResponse->getHeader('Content-Type'));
    }

    public function testSetResponseLoopFormatter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'The "Yiisoft\DataResponse\DataResponseFormatterInterface::format()"'
             . ' method should not return instance of "Yiisoft\DataResponse\DataResponse".'
        );

        $dataResponse = $this->createDataResponse('test');
        $dataResponse = $dataResponse->withResponseFormatter(new LoopDataResponseFormatter());
        $dataResponse->getBody()->rewind();
    }

    public function testSetEmptyDataWithoutFormatter(): void
    {
        $dataResponse = $this->createDataResponse(null);
        $dataResponse->getBody()->rewind();

        $this->assertSame('', $dataResponse->getBody()->getContents());
        $this->assertEmpty($dataResponse->getHeaders());
    }

    public function testSetNotStringData(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'The data is "integer" not a string. To get non-string data, use the'
            . ' "Yiisoft\DataResponse\DataResponse::getData()" method.'
        );
        $dataResponse = $this->createDataResponse(100);
        $dataResponse->getBody()->rewind();
    }

    public function testGetHeader(): void
    {
        $dataResponse = $this->createDataResponseFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());

        $this->assertSame(['application/json; charset=UTF-8'], $dataResponse->getHeader(Header::CONTENT_TYPE));
    }

    public function testGetHeaderLine(): void
    {
        $dataResponse = $this->createDataResponseFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());

        $this->assertSame('application/json; charset=UTF-8', $dataResponse->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testGetHeaders(): void
    {
        $dataResponse = $this->createDataResponseFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());

        $this->assertSame([Header::CONTENT_TYPE => ['application/json; charset=UTF-8']], $dataResponse->getHeaders());
    }

    public function testHasHeader(): void
    {
        $dataResponse = $this->createDataResponseFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());

        $this->assertTrue($dataResponse->hasHeader(Header::CONTENT_TYPE));
        $this->assertFalse($dataResponse->hasHeader(Header::ACCEPT_LANGUAGE));
    }

    public function testWithoutHeader(): void
    {
        $dataResponse = $this->createDataResponseFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse = $dataResponse->withoutHeader(Header::CONTENT_TYPE);
        $this->assertFalse($dataResponse->hasHeader(Header::CONTENT_TYPE));
    }

    public function testWithHeader(): void
    {
        $dataResponse = $this->createDataResponseFactory()->createResponse()
            ->withHeader(Header::CONTENT_TYPE, 'application/json');

        $this->assertSame(['Content-Type' => ['application/json']], $dataResponse->getHeaders());
    }

    public function testWithAddedHeader(): void
    {
        $dataResponse = $this->createDataResponseFactory()->createResponse()
            ->withHeader(Header::CONTENT_TYPE, 'application/json')
            ->withAddedHeader(Header::CONTENT_TYPE, 'application/xml');

        $this->assertSame(['Content-Type' => ['application/json', 'application/xml']], $dataResponse->getHeaders());
    }

    public function testGetStatusCode(): void
    {
        $factory = $this->createDataResponseFactory();

        $this->assertSame(200, $factory->createResponse()->getStatusCode());
        $this->assertSame(400, $factory->createResponse(null, Status::BAD_REQUEST)->getStatusCode());
    }

    public function testWithStatus(): void
    {
        $dataResponse = $this->createDataResponseFactory()->createResponse()->withStatus(Status::BAD_REQUEST, 'reason');

        $this->assertSame(400, $dataResponse->getStatusCode());
        $this->assertSame('reason', $dataResponse->getReasonPhrase());
    }

    public function testGetProtocolVersion(): void
    {
        $this->assertSame('1.1', $this->createDataResponseFactory()->createResponse()->getProtocolVersion());
    }

    public function testWithProtocolVersion(): void
    {
        $dataResponse = $this->createDataResponseFactory()->createResponse()->withProtocolVersion('1.0');
        $this->assertSame('1.0', $dataResponse->getProtocolVersion());
    }

    public function testWithBody(): void
    {
        $dataResponse = $this->createDataResponse('test1')->withBody($this->createStream('test2'));

        $dataResponse->getBody()->rewind();
        $this->assertSame('test1', $dataResponse->getBody()->getContents());
    }

    public function testWithBodyIfDataIsNull(): void
    {
        $dataResponse = $this->createDataResponse(null)->withBody($this->createStream('test2'));

        $dataResponse->getBody()->rewind();
        $this->assertSame('test2', $dataResponse->getBody()->getContents());
    }

    public function testWithData(): void
    {
        $dataResponse = $this->createDataResponse('test1');
        $dataResponse->getBody()->rewind();

        $dataResponse = $dataResponse->withData('test2');
        $dataResponse->getBody()->rewind();

        $this->assertSame('test2', $dataResponse->getBody()->getContents());
    }

    public function testGetData(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $this->assertSame('test', $dataResponse->getData());

        $dataResponse = $this->createDataResponse(fn () => 'test2');
        $this->assertSame('test2', $dataResponse->getData());
    }

    public function testHasData(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse->getBody()->rewind();

        $this->assertTrue($dataResponse->hasData());
    }

    public function testHasDataWithEmptyData(): void
    {
        $dataResponse = $this->createDataResponseFactory()->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse->getBody()->rewind();

        $this->assertFalse($dataResponse->hasData());
    }

    public function testSetResponseRecursiveFormatter(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $dataResponse = $dataResponse->withResponseFormatter(new RecursiveDataResponseFormatter());
        $dataResponse->getBody()->rewind();
        $this->assertTrue(true);
    }

    public function testDuplicateFormatting(): void
    {
        $formatter = new FakeDataResponseFormatter();
        $dataResponse = $this->createDataResponse('');
        $dataResponse = $dataResponse->withResponseFormatter($formatter);
        $dataResponse = $dataResponse->withData(['test']);
        $dataResponse->getBody()->rewind();

        $this->assertSame(1, $formatter->getTriggeredCount());
    }

    public function testGetDataImmutability(): void
    {
        $object = new stdClass();

        $dataResponse = $this->createDataResponse($object);

        $this->assertNotSame($object, $dataResponse->getData());
    }

    public function testImmutability(): void
    {
        $dataResponse = $this->createDataResponse(null);
        $this->assertNotSame($dataResponse, $dataResponse->withAddedHeader(Header::CONTENT_TYPE, 'application/xml'));
        $this->assertNotSame($dataResponse, $dataResponse->withBody($this->createStream('')));
        $this->assertNotSame($dataResponse, $dataResponse->withData(null));
        $this->assertNotSame($dataResponse, $dataResponse->withHeader(Header::CONTENT_TYPE, 'application/xml'));
        $this->assertNotSame($dataResponse, $dataResponse->withoutHeader(Header::CONTENT_TYPE));
        $this->assertNotSame($dataResponse, $dataResponse->withProtocolVersion('1.0'));
        $this->assertNotSame($dataResponse, $dataResponse->withResponseFormatter(new XmlDataResponseFormatter()));
        $this->assertNotSame($dataResponse, $dataResponse->withStatus(Status::ACCEPTED));
    }
}
