<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Formatter;

use HttpSoft\Message\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Stringable;
use Yiisoft\DataResponse\Formatter\DataEncodingException;
use Yiisoft\DataResponse\Formatter\HtmlFormatter;
use Yiisoft\Http\Header;

final class HtmlFormatterTest extends TestCase
{
    public static function dataFormatData(): iterable
    {
        yield 'null' => ['', null];
        yield 'string' => ['test', 'test'];
        yield 'empty string' => ['', ''];
        yield 'integer' => ['42', 42];
        yield 'float' => ['3.14', 3.14];
        yield 'bool true' => ['1', true];
        yield 'bool false' => ['', false];
        yield 'stringable object' => [
            'stringable content',
            new class implements Stringable {
                public function __toString(): string
                {
                    return 'stringable content';
                }
            },
        ];
    }

    #[DataProvider('dataFormatData')]
    public function testFormatData(string $expected, mixed $data): void
    {
        $formatter = new HtmlFormatter();

        $result = $formatter->formatData($data);

        $this->assertSame($expected, $result);
    }

    public static function dataFormatDataWithUnsupportedValue(): iterable
    {
        yield 'array' => [['test']];
        yield 'non-stringable object' => [new stdClass()];
        yield 'resource' => [fopen('php://memory', 'r')];
    }

    #[DataProvider('dataFormatDataWithUnsupportedValue')]
    public function testFormatDataWithUnsupportedValue(mixed $data): void
    {
        $formatter = new HtmlFormatter();

        $this->expectException(DataEncodingException::class);
        $this->expectExceptionMessage('Data must be either a scalar value, null, or a stringable object.');
        $formatter->formatData($data);
    }

    public function testFormatResponse(): void
    {
        $formatter = new HtmlFormatter();

        $response = $formatter->formatResponse(new Response());

        $this->assertSame('text/html; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testFormatResponseWithCustomContentType(): void
    {
        $formatter = new HtmlFormatter(contentType: 'text/xhtml');

        $response = $formatter->formatResponse(new Response());

        $this->assertSame('text/xhtml; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testFormatResponseWithCustomEncoding(): void
    {
        $formatter = new HtmlFormatter(encoding: 'ISO-8859-1');

        $response = $formatter->formatResponse(new Response());

        $this->assertSame('text/html; charset=ISO-8859-1', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testFormatResponseReplacesExistingContentTypeHeader(): void
    {
        $formatter = new HtmlFormatter();

        $response = $formatter->formatResponse(
            (new Response())->withHeader(Header::CONTENT_TYPE, 'text/plain'),
        );

        $this->assertSame('text/html; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }
}
