<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Formatter;

use HttpSoft\Message\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Stringable;
use Yiisoft\DataResponse\Formatter\DataEncodingException;
use Yiisoft\DataResponse\Formatter\PlainTextFormatter;
use Yiisoft\Http\Header;

final class PlainTextFormatterTest extends TestCase
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
        $formatter = new PlainTextFormatter();

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
        $formatter = new PlainTextFormatter();

        $this->expectException(DataEncodingException::class);
        $this->expectExceptionMessage('Data must be either a scalar value, null, or a stringable object.');
        $formatter->formatData($data);
    }

    public function testFormatResponse(): void
    {
        $formatter = new PlainTextFormatter();

        $response = $formatter->formatResponse(new Response());

        $this->assertSame('text/plain; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testFormatResponseWithCustomContentType(): void
    {
        $formatter = new PlainTextFormatter(contentType: 'text/csv');

        $response = $formatter->formatResponse(new Response());

        $this->assertSame('text/csv; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testFormatResponseWithCustomEncoding(): void
    {
        $formatter = new PlainTextFormatter(encoding: 'ISO-8859-1');

        $response = $formatter->formatResponse(new Response());

        $this->assertSame('text/plain; charset=ISO-8859-1', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testFormatResponseReplacesExistingContentTypeHeader(): void
    {
        $formatter = new PlainTextFormatter();

        $response = $formatter->formatResponse(
            (new Response())->withHeader(Header::CONTENT_TYPE, 'application/json'),
        );

        $this->assertSame('text/plain; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }
}
