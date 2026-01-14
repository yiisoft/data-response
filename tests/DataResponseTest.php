<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use stdClass;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\Formatter\XmlDataResponseFormatter;
use Yiisoft\DataResponse\Tests\Stub\CustomDataResponseFormatter;
use Yiisoft\DataResponse\Tests\Stub\FakeDataResponseFormatter;
use Yiisoft\DataResponse\Tests\Stub\LoopDataResponseFormatter;
use Yiisoft\DataResponse\Tests\Stub\RecursiveDataResponseFormatter;
use Yiisoft\DataResponse\Tests\Stub\ResponseFactoryWithCustomStream;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;

final class DataResponseTest extends TestCase
{
    public function testCreateResponse(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $dataResponse = $dataResponse->withHeader('Content-Type', 'application/json');
        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertInstanceOf(ResponseInterface::class, $dataResponse);
        $this->assertSame(
            ['application/json'],
            $dataResponse
                ->getResponse()
                ->getHeader('Content-Type'),
        );
        $this->assertSame(['application/json'], $dataResponse->getHeader('Content-Type'));
        $this->assertSame(
            $dataResponse
                ->getResponse()
                ->getBody(),
            $dataResponse->getBody(),
        );
        $this->assertSame(
            'test',
            $dataResponse
                ->getBody()
                ->getContents(),
        );
    }

    public function testCreateResponseThrowsExceptionIfStreamIsNotReadable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable.');

        $this->createDataResponseWithCustomResponseFactory(
            ResponseFactoryWithCustomStream::create('php://output'),
        );
    }

    public function testCreateResponseThrowsExceptionIfStreamIsNotSeekable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable.');

        $this->createDataResponseWithCustomResponseFactory(
            ResponseFactoryWithCustomStream::create('php://stdin'),
        );
    }

    public function testCreateResponseThrowsExceptionIfStreamIsNotWritable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable.');

        $this->createDataResponseWithCustomResponseFactory(
            ResponseFactoryWithCustomStream::create('php://input'),
        );
    }

    public function testCreateResponseThrowsExceptionIfResourceWasNotSeparatedFromStream(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Resource was not separated from the stream.');

        $this->createDataResponseWithCustomResponseFactory(
            ResponseFactoryWithCustomStream::createWithDisabledDetachMethod(),
        );
    }

    public function testChangeResponseData(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $data = $dataResponse->getData();
        $data .= '-changed';
        $dataResponse = $dataResponse->withData($data);
        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertSame(
            'test-changed',
            $dataResponse
                ->getBody()
                ->getContents(),
        );
    }

    public function testChangeResponseDataWithFormatter(): void
    {
        $dataResponse = $this->createDataResponse('test-value');
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse = $dataResponse->withData('new-test-value');
        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertSame(
            '"new-test-value"',
            $dataResponse
                ->getBody()
                ->getContents(),
        );
    }

    public function testSetResponseFormatter(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertTrue($dataResponse->hasResponseFormatter());
        $this->assertSame(
            '"test"',
            $dataResponse
                ->getBody()
                ->getContents(),
        );
        $this->assertSame(['application/json; charset=UTF-8'], $dataResponse->getHeader('Content-Type'));
    }

    public function testSetEmptyResponseFormatter(): void
    {
        $dataResponse = $this
            ->createDataResponseFactory()
            ->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertTrue($dataResponse->hasResponseFormatter());
        $this->assertSame(
            '',
            $dataResponse
                ->getBody()
                ->getContents(),
        );
        $this->assertSame(['application/json; charset=UTF-8'], $dataResponse->getHeader('Content-Type'));
    }

    public function testSetResponseLoopFormatter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'The "Yiisoft\DataResponse\DataResponseFormatterInterface::format()"'
             . ' method should not return instance of "Yiisoft\DataResponse\DataResponse".',
        );

        $dataResponse = $this->createDataResponse('test');
        $dataResponse = $dataResponse->withResponseFormatter(new LoopDataResponseFormatter());
        $dataResponse
            ->getBody()
            ->rewind();
    }

    public function testSetEmptyDataWithoutFormatter(): void
    {
        $dataResponse = $this->createDataResponse(null);
        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertSame(
            '',
            $dataResponse
                ->getBody()
                ->getContents(),
        );
        $this->assertEmpty($dataResponse->getHeaders());
    }

    public function testSetNotStringData(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'The data is "int" not a string. To get non-string data, use the'
            . ' "Yiisoft\DataResponse\DataResponse::getData()" method.',
        );
        $dataResponse = $this->createDataResponse(100);
        $dataResponse
            ->getBody()
            ->rewind();
    }

    public function testGetHeader(): void
    {
        $dataResponse = $this
            ->createDataResponseFactory()
            ->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());

        $this->assertSame(['application/json; charset=UTF-8'], $dataResponse->getHeader(Header::CONTENT_TYPE));
    }

    public function testGetHeaderLine(): void
    {
        $dataResponse = $this
            ->createDataResponseFactory()
            ->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());

        $this->assertSame('application/json; charset=UTF-8', $dataResponse->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testGetHeaders(): void
    {
        $dataResponse = $this
            ->createDataResponseFactory()
            ->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());

        $this->assertSame([Header::CONTENT_TYPE => ['application/json; charset=UTF-8']], $dataResponse->getHeaders());
    }

    public function testHasHeader(): void
    {
        $dataResponse = $this
            ->createDataResponseFactory()
            ->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());

        $this->assertTrue($dataResponse->hasHeader(Header::CONTENT_TYPE));
        $this->assertFalse($dataResponse->hasHeader(Header::ACCEPT_LANGUAGE));
    }

    public function testWithoutHeader(): void
    {
        $dataResponse = $this
            ->createDataResponseFactory()
            ->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse = $dataResponse->withoutHeader(Header::CONTENT_TYPE);
        $this->assertFalse($dataResponse->hasHeader(Header::CONTENT_TYPE));
    }

    public function testWithHeader(): void
    {
        $dataResponse = $this
            ->createDataResponseFactory()
            ->createResponse()
            ->withHeader(Header::CONTENT_TYPE, 'application/json')
        ;

        $this->assertSame(['Content-Type' => ['application/json']], $dataResponse->getHeaders());
    }

    public function testWithAddedHeader(): void
    {
        $dataResponse = $this
            ->createDataResponseFactory()
            ->createResponse()
            ->withHeader(Header::CONTENT_TYPE, 'application/json')
            ->withAddedHeader(Header::CONTENT_TYPE, 'application/xml')
        ;

        $this->assertSame(['Content-Type' => ['application/json', 'application/xml']], $dataResponse->getHeaders());
    }

    public function testGetStatusCode(): void
    {
        $factory = $this->createDataResponseFactory();

        $this->assertSame(
            200,
            $factory
                ->createResponse()
                ->getStatusCode(),
        );
        $this->assertSame(
            400,
            $factory
                ->createResponse(null, Status::BAD_REQUEST)
                ->getStatusCode(),
        );
    }

    public function testWithStatus(): void
    {
        $dataResponse = $this
            ->createDataResponseFactory()
            ->createResponse()
            ->withStatus(Status::BAD_REQUEST, 'reason');

        $this->assertSame(400, $dataResponse->getStatusCode());
        $this->assertSame('reason', $dataResponse->getReasonPhrase());
    }

    public function testGetProtocolVersion(): void
    {
        $this->assertSame(
            '1.1',
            $this
                ->createDataResponseFactory()
                ->createResponse()
                ->getProtocolVersion(),
        );
    }

    public function testWithProtocolVersion(): void
    {
        $dataResponse = $this
            ->createDataResponseFactory()
            ->createResponse()
            ->withProtocolVersion('1.0');
        $this->assertSame('1.0', $dataResponse->getProtocolVersion());
    }

    public function testWithBody(): void
    {
        $dataResponse = $this
            ->createDataResponse('test1')
            ->withBody($this->createStream('test2'));

        $dataResponse
            ->getBody()
            ->rewind();
        $this->assertSame(
            'test2',
            $dataResponse
                ->getBody()
                ->getContents(),
        );
    }

    public function testWithBodyAndWithResponseFormatter(): void
    {
        $dataResponse = $this
            ->createDataResponse('test1')
            ->withResponseFormatter(new JsonDataResponseFormatter())
            ->withBody($this->createStream('test2'))
        ;

        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertSame(['application/json; charset=UTF-8'], $dataResponse->getHeader(Header::CONTENT_TYPE));
        $this->assertSame(
            'test2',
            $dataResponse
                ->getBody()
                ->getContents(),
        );

        $dataResponse = $dataResponse
            ->withBody($this->createStream('test3'))
            ->withResponseFormatter(new XmlDataResponseFormatter())
        ;

        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertSame(['application/xml; charset=UTF-8'], $dataResponse->getHeader(Header::CONTENT_TYPE));
        $this->assertSame(
            'test3',
            $dataResponse
                ->getBody()
                ->getContents(),
        );
    }

    public function testWithBodyIfDataIsNull(): void
    {
        $dataResponse = $this
            ->createDataResponse(null)
            ->withBody($this->createStream('test'));

        $dataResponse
            ->getBody()
            ->rewind();
        $this->assertSame(
            'test',
            $dataResponse
                ->getBody()
                ->getContents(),
        );
    }

    public function testWithBodyIfDataIsNullWhenOverride(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $dataResponse
            ->getBody()
            ->rewind();

        $dataResponse = $dataResponse->withData(null);
        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertSame(
            '',
            $dataResponse
                ->getBody()
                ->getContents(),
        );
    }

    public function testWithData(): void
    {
        $dataResponse = $this->createDataResponse('test1');
        $dataResponse
            ->getBody()
            ->rewind();

        $dataResponse = $dataResponse->withData('test2');
        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertSame(
            'test2',
            $dataResponse
                ->getBody()
                ->getContents(),
        );
    }

    public function testWithDataMultipleCalls(): void
    {
        $dataResponse = $this->createDataResponse('test1');
        $dataResponse
            ->getBody()
            ->rewind();

        $dataResponse = $dataResponse->withData('test2');
        $this->assertSame(
            5,
            $dataResponse
                ->getBody()
                ->tell(),
        );
        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertSame(
            'test2',
            $dataResponse
                ->getBody()
                ->getContents(),
        );

        $dataResponse = $dataResponse->withData('test3');
        $this->assertSame(
            5,
            $dataResponse
                ->getBody()
                ->tell(),
        );
        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertSame(
            'test3',
            $dataResponse
                ->getBody()
                ->getContents(),
        );
    }

    public function testWithDataThrowsExceptionIfWithBodyWasCalled(): void
    {
        $dataResponse = $this->createDataResponse('test1');
        $dataResponse
            ->getBody()
            ->rewind();

        $dataResponse = $dataResponse->withBody($this->createStream('test2'));
        $dataResponse
            ->getBody()
            ->rewind();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'The data cannot be set because the body was previously forced to be set'
            . ' using the "Yiisoft\DataResponse\DataResponse::withBody()" method.',
        );

        $dataResponse->withData('test3');
    }

    public static function dataEdgeCaseWithKeepBodyPositionAfterCallGetter(): iterable
    {
        yield [fn(DataResponse $dataResponse) => $dataResponse->withAddedHeader('X-Test', '42')];
        yield [fn(DataResponse $dataResponse) => $dataResponse->withHeader('X-Test', '42')];
        yield [fn(DataResponse $dataResponse) => $dataResponse->withProtocolVersion('1.1')];
        yield [fn(DataResponse $dataResponse) => $dataResponse->withStatus(200)];
        yield [fn(DataResponse $dataResponse) => $dataResponse->withResponseFormatter(new JsonDataResponseFormatter())];
    }

    #[DataProvider('dataEdgeCaseWithKeepBodyPositionAfterCallGetter')]
    public function testEdgeCaseWithKeepBodyPositionAfterCallGetter(Closure $closure): void
    {
        $dataResponse = $this
            ->createDataResponse('test')
            ->withResponseFormatter(new JsonDataResponseFormatter());

        $dataResponse->getBody();
        $dataResponse = $closure($dataResponse);

        $dataResponse->getBody()->rewind();

        $dataResponse->getStatusCode();

        $this->assertSame('"test"', $dataResponse->getBody()->getContents());
    }

    public function testGetData(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $this->assertSame('test', $dataResponse->getData());

        $dataResponse = $this->createDataResponse(fn() => 'test2');
        $this->assertSame('test2', $dataResponse->getData());
    }

    public function testHasData(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertTrue($dataResponse->hasData());
    }

    public function testHasDataWithEmptyData(): void
    {
        $dataResponse = $this
            ->createDataResponseFactory()
            ->createResponse();
        $dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
        $dataResponse
            ->getBody()
            ->rewind();

        $this->assertFalse($dataResponse->hasData());
    }

    public function testSetResponseRecursiveFormatter(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $dataResponse = $dataResponse->withResponseFormatter(new RecursiveDataResponseFormatter());
        $dataResponse
            ->getBody()
            ->rewind();
        $this->assertTrue(true);
    }

    public function testDuplicateFormatting(): void
    {
        $formatter = new FakeDataResponseFormatter();
        $dataResponse = $this->createDataResponse('');
        $dataResponse = $dataResponse->withResponseFormatter($formatter);
        $dataResponse = $dataResponse->withData(['test']);
        $dataResponse
            ->getBody()
            ->rewind();

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

    public function testFormatterCouldChangeStatusCode(): void
    {
        $formatter = (new CustomDataResponseFormatter())->withStatusCode(410);
        $dataResponse = $this
            ->createDataResponse(['test'])
            ->withResponseFormatter($formatter)
            ->withStatus(200);

        $this->assertSame(410, $dataResponse->getStatusCode());
    }

    public function testFormatterCouldChangeHeaders(): void
    {
        $formatter = (new CustomDataResponseFormatter())->withHeaders(['Content-Type' => 'Custom']);
        $dataResponse = $this
            ->createDataResponse(['test'])
            ->withResponseFormatter($formatter)
            ->withHeader('Content-Type', 'plain/text')
        ;

        $this->assertSame('Custom', $dataResponse->getHeaderLine('Content-Type'));
    }

    public function testFormatterCouldChangeAndAddHeaders(): void
    {
        $formatter = (new CustomDataResponseFormatter())->withHeaders(['Content-Type' => 'Custom']);
        $dataResponse = $this
            ->createDataResponse(['test'])
            ->withResponseFormatter($formatter)
            ->withHeader('Content-Type', 'plain/text')
            ->withAddedHeader('Content-Type', 'plain/html')
        ;

        $this->assertSame('Custom', $dataResponse->getHeaderLine('Content-Type'));
    }

    public function testFormatterCouldChangeProtocol(): void
    {
        $formatter = (new CustomDataResponseFormatter())->withProtocol('2.0');
        $dataResponse = $this
            ->createDataResponse(['test'])
            ->withResponseFormatter($formatter)
            ->withProtocolVersion('1.0')
        ;

        $this->assertSame('2.0', $dataResponse->getProtocolVersion());
    }

    public function testFormatterCouldChangeReasonPhrase(): void
    {
        $formatter = (new CustomDataResponseFormatter())->withReasonPhrase('Reason');
        $dataResponse = $this
            ->createDataResponse(['test'])
            ->withResponseFormatter($formatter)
            ->withStatus(200, 'OK')
        ;

        $this->assertSame('Reason', $dataResponse->getReasonPhrase());
    }
}
