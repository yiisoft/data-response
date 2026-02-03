<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Middleware;

use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequest;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Middleware\PlainTextDataResponseMiddleware;
use Yiisoft\DataResponse\Tests\Support\StubRequestHandler;
use Yiisoft\Http\Header;

final class PlainTextDataResponseMiddlewareTest extends TestCase
{
    public function testProcess(): void
    {
        $dataStream = new DataStream('test content');
        $response = (new Response())->withBody($dataStream);
        $middleware = new PlainTextDataResponseMiddleware();

        $result = $middleware->process(
            new ServerRequest(),
            new StubRequestHandler($response),
        );

        $this->assertSame('test content', (string) $result->getBody());
        $this->assertSame('text/plain; charset=UTF-8', $result->getHeaderLine(Header::CONTENT_TYPE));
    }
}
