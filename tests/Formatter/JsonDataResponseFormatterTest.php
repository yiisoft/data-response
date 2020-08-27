<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Formatter;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\Http\Header;

class JsonDataResponseFormatterTest extends TestCase
{
    public function testFormatter(): void
    {
        $dataResponse = $this->createFactory()->createResponse(['test' => 'test']);
        $result = (new JsonDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame('{"test":"test"}', $result->getBody()->getContents());
        $this->assertSame(['application/json'], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testFormatterWithContentType(): void
    {
        $dataResponse = $this->createFactory()->createResponse(['test' => 'test']);
        $result = (new JsonDataResponseFormatter())->withContentType('application/xml')->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame('{"test":"test"}', $result->getBody()->getContents());
        $this->assertSame(['application/xml'], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testFormatterWithOptions(): void
    {
        $dataResponse = $this->createFactory()->createResponse(['test']);
        $result = (new JsonDataResponseFormatter())->withOptions(JSON_FORCE_OBJECT)->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame('{"0":"test"}', $result->getBody()->getContents());
        $this->assertSame(['application/json'], $result->getHeader(Header::CONTENT_TYPE));
    }

    private function createFactory(): DataResponseFactory
    {
        return new DataResponseFactory(new Psr17Factory());
    }
}
