<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Middleware;

use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequest;
use HttpSoft\Message\StreamFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\HtmlFormatter;
use Yiisoft\DataResponse\Formatter\JsonFormatter;
use Yiisoft\DataResponse\Formatter\PlainTextFormatter;
use Yiisoft\DataResponse\Formatter\XmlFormatter;
use Yiisoft\DataResponse\Middleware\ContentNegotiatorDataResponseMiddleware;
use Yiisoft\DataResponse\Tests\Support\StubRequestHandler;
use Yiisoft\Http\Header;

final class ContentNegotiatorDataResponseMiddlewareTest extends TestCase
{
    public function testProcessWithMatchingAcceptHeader(): void
    {
        $dataStream = new DataStream(['key' => 'value']);
        $response = (new Response())->withBody($dataStream);
        $middleware = new ContentNegotiatorDataResponseMiddleware([
            'application/json' => new JsonFormatter(),
            'application/xml' => new XmlFormatter(),
        ]);

        $result = $middleware->process(
            (new ServerRequest())->withHeader(Header::ACCEPT, 'application/json'),
            new StubRequestHandler($response),
        );

        $this->assertSame('{"key":"value"}', (string) $result->getBody());
        $this->assertSame('application/json; charset=UTF-8', $result->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testProcessWithMultipleAcceptHeaders(): void
    {
        $dataStream = new DataStream(['key' => 'value']);
        $response = (new Response())->withBody($dataStream);
        $middleware = new ContentNegotiatorDataResponseMiddleware([
            'application/json' => new JsonFormatter(),
            'application/xml' => new XmlFormatter(),
        ]);

        $result = $middleware->process(
            (new ServerRequest())->withHeader(
                Header::ACCEPT,
                'text/html, application/xml;q=0.9, application/json;q=0.8',
            ),
            new StubRequestHandler($response),
        );

        $this->assertSame('application/xml; charset=UTF-8', $result->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testProcessWithNoMatchingAcceptHeaderUsesFallback(): void
    {
        $dataStream = new DataStream('test content');
        $response = (new Response())->withBody($dataStream);
        $middleware = new ContentNegotiatorDataResponseMiddleware(
            ['application/json' => new JsonFormatter()],
            new PlainTextFormatter(),
        );

        $result = $middleware->process(
            (new ServerRequest())->withHeader(Header::ACCEPT, 'text/html'),
            new StubRequestHandler($response),
        );

        $this->assertSame('test content', (string) $result->getBody());
        $this->assertSame('text/plain; charset=UTF-8', $result->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testProcessWithNoMatchingAcceptHeaderAndNoFallback(): void
    {
        $dataStream = new DataStream(['key' => 'value']);
        $response = (new Response())->withBody($dataStream);
        $middleware = new ContentNegotiatorDataResponseMiddleware([
            'application/json' => new JsonFormatter(),
        ]);

        $result = $middleware->process(
            (new ServerRequest())->withHeader(Header::ACCEPT, 'text/html'),
            new StubRequestHandler($response),
        );

        $this->assertSame($response, $result);
    }

    public function testProcessWithNonDataStreamBody(): void
    {
        $stream = (new StreamFactory())->createStream('plain content');
        $response = (new Response())->withBody($stream);
        $middleware = new ContentNegotiatorDataResponseMiddleware([
            'application/json' => new JsonFormatter(),
        ]);

        $result = $middleware->process(
            (new ServerRequest())->withHeader(Header::ACCEPT, 'application/json'),
            new StubRequestHandler($response),
        );

        $this->assertSame($response, $result);
    }

    public function testProcessWithDataStreamWithFormatter(): void
    {
        $dataStream = new DataStream(['key' => 'value'], new HtmlFormatter());
        $response = (new Response())->withBody($dataStream);
        $middleware = new ContentNegotiatorDataResponseMiddleware([
            'application/json' => new JsonFormatter(),
        ]);

        $result = $middleware->process(
            (new ServerRequest())->withHeader(Header::ACCEPT, 'application/json'),
            new StubRequestHandler($response),
        );

        $this->assertSame($response, $result);
    }

    public function testConstructorWithInvalidContentType(): void
    {
        $formatters = [new JsonFormatter()];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid formatter content type. A string is expected, "integer" is received.');
        new ContentNegotiatorDataResponseMiddleware($formatters);
    }

    public function testConstructorWithInvalidFormatter(): void
    {
        $formatters = [
            'application/json' => new stdClass(),
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Invalid formatter. A "Yiisoft\DataResponse\Formatter\FormatterInterface" instance is expected, "stdClass" is received.'
        );
        new ContentNegotiatorDataResponseMiddleware($formatters);
    }
}
