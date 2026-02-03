<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Formatter;

use ArrayIterator;
use HttpSoft\Message\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\Formatter\XmlDataInterface;
use Yiisoft\DataResponse\Formatter\XmlFormatter;
use Yiisoft\Http\Header;

use function sprintf;

final class XmlFormatterTest extends TestCase
{
    public static function dataFormatDataWithEmptyData(): iterable
    {
        yield 'null' => [null];
        yield 'empty array' => [[]];
        yield 'empty string' => [''];
        yield 'zero' => [0];
        yield 'false' => [false];
    }

    #[DataProvider('dataFormatDataWithEmptyData')]
    public function testFormatDataWithEmptyData(mixed $data): void
    {
        $formatter = new XmlFormatter();

        $result = $formatter->formatData($data);

        $this->assertSame('', $result);
    }

    public static function dataFormatData(): iterable
    {
        yield 'string' => [
            self::xml('<response>test</response>'),
            'test',
        ];
        yield 'integer' => [
            self::xml('<response>42</response>'),
            42,
        ];
        yield 'float' => [
            self::xml('<response>3.14</response>'),
            3.14,
        ];
        yield 'bool true' => [
            self::xml('<response>true</response>'),
            true,
        ];
        yield 'simple array' => [
            self::xml('<response><item>a</item><item>b</item></response>'),
            ['a', 'b'],
        ];
        yield 'associative array' => [
            self::xml('<response><key>value</key></response>'),
            ['key' => 'value'],
        ];
        yield 'nested array' => [
            self::xml('<response><parent><child>value</child></parent></response>'),
            ['parent' => ['child' => 'value']],
        ];
        yield 'nested empty array' => [
            self::xml('<response><parent/></response>'),
            ['parent' => []],
        ];
        yield 'mixed array' => [
            self::xml('<response><name>test</name><count>5</count><active>true</active></response>'),
            ['name' => 'test', 'count' => 5, 'active' => true],
        ];
        yield 'bool false in array' => [
            self::xml('<response><enabled>false</enabled></response>'),
            ['enabled' => false],
        ];
        yield 'invalid xml tag name' => [
            self::xml('<response><item>value</item></response>'),
            ['1invalid' => 'value'],
        ];
        yield 'object' => [
            self::xml('<response><item><name>test</name><value>42</value></item></response>'),
            (object) ['name' => 'test', 'value' => 42],
        ];
        yield 'traversable' => [
            self::xml('<response><item>a</item><item>b</item><item>c</item></response>'),
            new ArrayIterator(['a', 'b', 'c']),
        ];
        yield 'traversable in array' => [
            self::xml('<response><items><item>a</item><item>b</item></items></response>'),
            ['items' => new ArrayIterator(['a', 'b'])],
        ];
        yield 'XmlDataInterface' => [
            self::xml('<response><custom id="1" type="test"><name>value</name></custom></response>'),
            new class () implements XmlDataInterface {
                public function xmlTagName(): string
                {
                    return 'custom';
                }

                public function xmlTagAttributes(): array
                {
                    return ['id' => '1', 'type' => 'test'];
                }

                public function xmlData(): array
                {
                    return ['name' => 'value'];
                }
            },
        ];
        yield 'nested XmlDataInterface' => [
            self::xml('<response><items><inner><value>nested</value></inner></items></response>'),
            [
                'items' => [
                    new class () implements XmlDataInterface {
                        public function xmlTagName(): string
                        {
                            return 'inner';
                        }

                        public function xmlTagAttributes(): array
                        {
                            return [];
                        }

                        public function xmlData(): array
                        {
                            return ['value' => 'nested'];
                        }
                    },
                ],
            ],
        ];
    }

    #[DataProvider('dataFormatData')]
    public function testFormatData(string $expected, mixed $data): void
    {
        $formatter = new XmlFormatter();

        $result = $formatter->formatData($data);

        $this->assertSame($expected, $result);
    }

    public function testFormatDataWithCustomRootTag(): void
    {
        $formatter = new XmlFormatter(rootTag: 'data');

        $result = $formatter->formatData(['key' => 'value']);

        $this->assertSame(
            self::xml('<data><key>value</key></data>'),
            $result
        );
    }

    public function testFormatDataWithEmptyRootTag(): void
    {
        $formatter = new XmlFormatter(rootTag: '');

        $result = $formatter->formatData(['key' => 'value']);

        $this->assertSame(
            self::xml('<key>value</key>'),
            $result
        );
    }

    public function testFormatDataWithCustomVersion(): void
    {
        $formatter = new XmlFormatter(version: '1.1');

        $result = $formatter->formatData(['key' => 'value']);

        $this->assertSame(
            self::xml('<response><key>value</key></response>', '1.1'),
            $result
        );
    }

    public function testFormatDataWithCustomEncoding(): void
    {
        $formatter = new XmlFormatter(encoding: 'ISO-8859-1');

        $result = $formatter->formatData(['key' => 'value']);

        $this->assertSame(
            self::xml('<response><key>value</key></response>', encoding: 'ISO-8859-1'),
            $result
        );
    }

    public function testFormatResponse(): void
    {
        $formatter = new XmlFormatter();

        $response = $formatter->formatResponse(new Response());

        $this->assertSame('application/xml; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testFormatResponseWithCustomContentType(): void
    {
        $formatter = new XmlFormatter(contentType: 'text/xml');

        $response = $formatter->formatResponse(new Response());

        $this->assertSame('text/xml; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testFormatResponseWithCustomEncoding(): void
    {
        $formatter = new XmlFormatter(encoding: 'ISO-8859-1');

        $response = $formatter->formatResponse(new Response());

        $this->assertSame('application/xml; charset=ISO-8859-1', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testFormatResponseReplacesExistingContentTypeHeader(): void
    {
        $formatter = new XmlFormatter();

        $response = $formatter->formatResponse(
            (new Response())->withHeader(Header::CONTENT_TYPE, 'text/plain')
        );

        $this->assertSame('application/xml; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    private static function xml(string $content, string $version = '1.0', string $encoding = 'UTF-8'): string
    {
        return sprintf('<?xml version="%s" encoding="%s"?>%s', $version, $encoding, "\n" . $content . "\n");
    }
}
