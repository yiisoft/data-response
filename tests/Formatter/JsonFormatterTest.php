<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Formatter;

use HttpSoft\Message\Response;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Yiisoft\DataResponse\Formatter\JsonFormatter;
use Yiisoft\Http\Header;

use const JSON_FORCE_OBJECT;
use const JSON_PRETTY_PRINT;

final class JsonFormatterTest extends TestCase
{
    public static function dataFormatData(): iterable
    {
        yield 'null' => ['null', null];
        yield 'string' => ['"test"', 'test'];
        yield 'empty string' => ['""', ''];
        yield 'integer' => ['42', 42];
        yield 'float' => ['3.14', 3.14];
        yield 'bool true' => ['true', true];
        yield 'bool false' => ['false', false];
        yield 'array' => ['["a","b"]', ['a', 'b']];
        yield 'associative array' => ['{"key":"value"}', ['key' => 'value']];
        yield 'object' => ['{"property":"value"}', (object) ['property' => 'value']];
        yield 'unicode' => ['"тест"', 'тест'];
        yield 'slashes' => ['"/path/to/file"', '/path/to/file'];
    }

    #[DataProvider('dataFormatData')]
    public function testFormatData(string $expected, mixed $data): void
    {
        $formatter = new JsonFormatter();

        $result = $formatter->formatData($data);

        $this->assertSame($expected, $result);
    }

    public function testFormatDataWithUnsupportedValue(): void
    {
        $formatter = new JsonFormatter();
        $resource = fopen('php://memory', 'r');

        $this->expectException(JsonException::class);
        $formatter->formatData($resource);
    }

    public function testFormatDataWithCustomOptions(): void
    {
        $formatter = new JsonFormatter(options: JSON_FORCE_OBJECT);

        $result = $formatter->formatData(['a', 'b']);

        $this->assertSame('{"0":"a","1":"b"}', $result);
    }

    public function testFormatDataWithPrettyPrint(): void
    {
        $formatter = new JsonFormatter(options: JSON_PRETTY_PRINT);

        $result = $formatter->formatData(['key' => 'value']);

        $this->assertSame("{\n    \"key\": \"value\"\n}", $result);
    }

    public function testFormatResponse(): void
    {
        $formatter = new JsonFormatter();

        $response = $formatter->formatResponse(new Response());

        $this->assertSame('application/json; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testFormatResponseWithCustomContentType(): void
    {
        $formatter = new JsonFormatter(contentType: 'application/vnd.api+json');

        $response = $formatter->formatResponse(new Response());

        $this->assertSame('application/vnd.api+json; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testFormatResponseWithCustomEncoding(): void
    {
        $formatter = new JsonFormatter(encoding: 'ISO-8859-1');

        $response = $formatter->formatResponse(new Response());

        $this->assertSame('application/json; charset=ISO-8859-1', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testFormatResponseReplacesExistingContentTypeHeader(): void
    {
        $formatter = new JsonFormatter();

        $response = $formatter->formatResponse(
            (new Response())->withHeader(Header::CONTENT_TYPE, 'text/plain')
        );

        $this->assertSame('application/json; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }
}
