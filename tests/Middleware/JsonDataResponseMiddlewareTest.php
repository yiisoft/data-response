<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Middleware;

use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequest;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Middleware\JsonDataResponseMiddleware;
use Yiisoft\DataResponse\Tests\Support\StubRequestHandler;
use Yiisoft\Http\Header;

final class JsonDataResponseMiddlewareTest extends TestCase
{
    public function testProcess(): void
    {
        $dataStream = new DataStream(['key' => 'value']);
        $response = (new Response())->withBody($dataStream);
        $middleware = new JsonDataResponseMiddleware();

        $result = $middleware->process(
            new ServerRequest(),
            new StubRequestHandler($response),
        );

        $this->assertSame('{"key":"value"}', (string) $result->getBody());
        $this->assertSame('application/json; charset=UTF-8', $result->getHeaderLine(Header::CONTENT_TYPE));
    }
}
