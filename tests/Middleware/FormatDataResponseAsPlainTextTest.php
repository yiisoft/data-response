<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Middleware;

use Yiisoft\DataResponse\Formatter\PlainTextDataResponseFormatter;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsPlainText;
use Yiisoft\DataResponse\Tests\TestCase;

final class FormatDataResponseAsPlainTextTest extends TestCase
{
    public function testBase(): void
    {
        $middleware = new FormatDataResponseAsPlainText(new PlainTextDataResponseFormatter());
        $dataResponse = $this->createDataResponse('test');

        $response = $middleware->process(
            $this->createRequest(),
            $this->createRequestHandler($dataResponse)
        );

        $response->getBody()->rewind();

        $this->assertSame('test', $response->getBody()->getContents());
        $this->assertSame(['text/plain; charset=UTF-8'], $response->getHeader('Content-Type'));
    }
}
