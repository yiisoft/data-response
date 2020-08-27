<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Formatter;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\Formatter\HtmlDataResponseFormatter;

class HtmlDataResponseFormatterTest extends TestCase
{
    public function testFormatter(): void
    {
        $dataResponse = $this->createFactory()->createResponse('test');
        $result = (new HtmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame('test', $result->getBody()->getContents());
        $this->assertSame(['text/html; charset=UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testFormatterWithEncoding(): void
    {
        $dataResponse = $this->createFactory()->createResponse('test');
        $result = (new HtmlDataResponseFormatter())->withEncoding('ISO-8859-1')->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame('test', $result->getBody()->getContents());
        $this->assertSame(['text/html; charset=ISO-8859-1'], $result->getHeader('Content-Type'));
    }

    public function testFormatterWithContentType(): void
    {
        $dataResponse = $this->createFactory()->createResponse('test');
        $result = (new HtmlDataResponseFormatter())->withContentType('text/plain')->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame('test', $result->getBody()->getContents());
        $this->assertSame('text/plain; charset=UTF-8', $result->getHeaderLine('Content-Type'));
    }

    private function createFactory(): DataResponseFactory
    {
        return new DataResponseFactory(new Psr17Factory());
    }
}
