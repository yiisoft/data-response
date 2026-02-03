<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Middleware;

use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequest;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Middleware\XmlDataResponseMiddleware;
use Yiisoft\DataResponse\Tests\Support\StubRequestHandler;
use Yiisoft\Http\Header;

final class XmlDataResponseMiddlewareTest extends TestCase
{
    public function testProcess(): void
    {
        $dataStream = new DataStream(['key' => 'value']);
        $response = (new Response())->withBody($dataStream);
        $middleware = new XmlDataResponseMiddleware();

        $result = $middleware->process(
            new ServerRequest(),
            new StubRequestHandler($response),
        );

        $this->assertSame(
            <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <response><key>value</key></response>

            XML,
            (string) $result->getBody(),
        );
        $this->assertSame('application/xml; charset=UTF-8', $result->getHeaderLine(Header::CONTENT_TYPE));
    }
}
