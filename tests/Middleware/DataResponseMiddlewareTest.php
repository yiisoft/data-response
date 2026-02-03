<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Middleware;

use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequest;
use HttpSoft\Message\StreamFactory;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\Tests\Support\StubRequestHandler;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\JsonFormatter;
use Yiisoft\DataResponse\Formatter\PlainTextFormatter;
use Yiisoft\DataResponse\Middleware\DataResponseMiddleware;
use Yiisoft\Http\Header;

final class DataResponseMiddlewareTest extends TestCase
{
    public function testProcessWithDataStreamWithoutFormatter(): void
    {
        $data = ['key' => 'value'];
        $dataStream = new DataStream($data);
        $response = (new Response())->withBody($dataStream);
        $middleware = new DataResponseMiddleware(new JsonFormatter());

        $result = $middleware->process(
            new ServerRequest(),
            new StubRequestHandler($response),
        );

        $this->assertSame('{"key":"value"}', (string) $result->getBody());
        $this->assertSame('application/json; charset=UTF-8', $result->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testProcessWithDataStreamWithFormatter(): void
    {
        $data = ['key' => 'value'];
        $dataStream = new DataStream($data, new PlainTextFormatter());
        $response = (new Response())->withBody($dataStream);
        $middleware = new DataResponseMiddleware(new JsonFormatter());

        $result = $middleware->process(
            new ServerRequest(),
            new StubRequestHandler($response),
        );

        $this->assertSame($response, $result);
    }

    public function testProcessWithNonDataStreamBody(): void
    {
        $stream = (new StreamFactory())->createStream('plain content');
        $response = (new Response())->withBody($stream);
        $middleware = new DataResponseMiddleware(new JsonFormatter());

        $result = $middleware->process(
            new ServerRequest(),
            new StubRequestHandler($response),
        );

        $this->assertSame($response, $result);
    }
}
