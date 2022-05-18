<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Formatter;

use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\Tests\TestCase;
use Yiisoft\Http\Header;

final class JsonDataResponseFormatterTest extends TestCase
{
    public function testCorrectFormat(): void
    {
        $dataResponse = $this->createDataResponse(['test' => 'test']);
        $result = (new JsonDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            '{"test":"test"}',
            $result
                ->getBody()
                ->getContents(),
        );
        $this->assertSame(['application/json; charset=UTF-8'], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testWithContentType(): void
    {
        $dataResponse = $this->createDataResponse(['test' => 'test']);
        $result = (new JsonDataResponseFormatter())
            ->withContentType('application/xml')
            ->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            '{"test":"test"}',
            $result
                ->getBody()
                ->getContents(),
        );
        $this->assertSame(['application/xml; charset=UTF-8'], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testWithOptions(): void
    {
        $dataResponse = $this->createDataResponse(['test']);
        $result = (new JsonDataResponseFormatter())
            ->withOptions(JSON_FORCE_OBJECT)
            ->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            '{"0":"test"}',
            $result
                ->getBody()
                ->getContents(),
        );
        $this->assertSame(['application/json; charset=UTF-8'], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testWithEmptyResponse(): void
    {
        $dataResponse = $this->createDataResponse(null);
        $result = (new JsonDataResponseFormatter())
            ->withOptions(JSON_FORCE_OBJECT)
            ->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            '',
            $result
                ->getBody()
                ->getContents(),
        );
        $this->assertSame(['application/json; charset=UTF-8'], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testImmutability(): void
    {
        $formatter = new JsonDataResponseFormatter();
        $this->assertNotSame($formatter, $formatter->withContentType('text/plain'));
        $this->assertNotSame($formatter, $formatter->withEncoding('utf-8'));
        $this->assertNotSame($formatter, $formatter->withOptions(JSON_BIGINT_AS_STRING));
    }
}
