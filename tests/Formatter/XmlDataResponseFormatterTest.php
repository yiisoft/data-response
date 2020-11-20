<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Formatter;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use function preg_replace;
use function sprintf;
use stdClass;
use Yiisoft\DataResponse\DataResponse;

use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\Formatter\XmlDataResponseFormatter;

class XmlDataResponseFormatterTest extends TestCase
{
    public function testCorrectFormat(): void
    {
        $dataResponse = $this->createResponse('test');
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml('<response>test</response>'),
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testWithEncoding(): void
    {
        $encoding = 'ISO-8859-1';
        $dataResponse = $this->createResponse('test');
        $result = (new XmlDataResponseFormatter())->withEncoding($encoding)->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml('<response>test</response>', '1.0', $encoding),
            $result->getBody()->getContents()
        );
        $this->assertSame(["application/xml; {$encoding}"], $result->getHeader('Content-Type'));
    }

    public function testWithVersion(): void
    {
        $version = '1.1';
        $dataResponse = $this->createResponse('test');
        $result = (new XmlDataResponseFormatter())->withVersion($version)->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml('<response>test</response>', $version),
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testWithRootTag(): void
    {
        $dataResponse = $this->createResponse('test');
        $result = (new XmlDataResponseFormatter())->withRootTag('exampleRootTag')->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml('<exampleRootTag>test</exampleRootTag>'),
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testWithContentType(): void
    {
        $dataResponse = $this->createResponse('test');
        $result = (new XmlDataResponseFormatter())->withContentType('text/xml')->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml('<response>test</response>'),
            $result->getBody()->getContents()
        );
        $this->assertSame(['text/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testScalarValues(): void
    {
        $dataResponse = $this->createResponse([true, false, 100.2]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <item key="0">true</item>
                        <item key="1">false</item>
                        <item key="2">100.2</item>
                    </response>
                EOF
            ),
            $result->getBody()->getContents()
        );
    }

    public function testArrayValues(): void
    {
        $dataResponse = $this->createResponse([
            [100 => [], '200' => null],
            [1, 1.1, 'foo' => 'bar', true, false],
        ]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <item key="0">
                            <item key="100"/>
                            <item key="200"/>
                        </item>
                        <item key="1">
                            <item key="0">1</item>
                            <item key="1">1.1</item>
                            <foo>bar</foo>
                            <item key="2">true</item>
                            <item key="3">false</item>
                        </item>
                    </response>
                EOF
            ),
            $result->getBody()->getContents()
        );
    }

    public function testEmptyObjectValues(): void
    {
        $dataResponse = $this->createResponse(['object' => new stdClass()]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml('<response><object/></response>'),
            $result->getBody()->getContents()
        );
    }

    public function testObjectValues(): void
    {
        $object = $this->createDummyObject('foo', 99, 1.1, [1, 'foo' => 'bar']);
        $dataResponse = $this->createResponse($object);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <string>{$object->getString()}</string>
                        <int>{$object->getInt()}</int>
                        <float>{$object->getFloat()}</float>
                        <array>
                            <item key="0">1</item>
                            <foo>bar</foo>
                        </array>
                    </response>
                EOF
            ),
            $result->getBody()->getContents()
        );
    }

    public function testArrayObjectValues(): void
    {
        $objects = [
            $object1 = $this->createDummyObject('foo', 99, 1.1, ['foo', 1.1]),
            $object2 = $this->createDummyObject('bar', 10, 2.2, [1, 2, 3]),
            $object3 = $this->createDummyObject('baz', 0, 3.3, ['bar' => 'baz']),
        ];
        $dataResponse = $this->createResponse($objects);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <item key="0">
                            <string>{$object1->getString()}</string>
                            <int>{$object1->getInt()}</int>
                            <float>{$object1->getFloat()}</float>
                            <array>foo</array>
                            <array>1.1</array>
                        </item>
                        <item key="1">
                            <string>{$object2->getString()}</string>
                            <int>{$object2->getInt()}</int>
                            <float>{$object2->getFloat()}</float>
                            <array>1</array>
                            <array>2</array>
                            <array>3</array>
                        </item>
                        <item key="2">
                            <string>{$object3->getString()}</string>
                            <int>{$object3->getInt()}</int>
                            <float>{$object3->getFloat()}</float>
                            <array><bar>baz</bar></array>
                        </item>
                    </response>
                EOF
            ),
            $result->getBody()->getContents()
        );
    }

    private function createResponse($data): DataResponse
    {
        return (new DataResponseFactory(new Psr17Factory()))->createResponse($data);
    }

    private function xml(string $data, string $version = '1.0', string $encoding = 'UTF-8'): string
    {
        $startLine = sprintf('<?xml version="%s" encoding="%s"?>', $version, $encoding);
        return $startLine . "\n" . preg_replace('/(?!item)\s(?!key)/', '', $data) . "\n";
    }

    private function createDummyObject(string $string, int $int, float $float, array $array): object
    {
        return new class($string, $int, $float, $array) {
            private string $string;
            private int $int;
            private float $float;
            private array $array;

            public function __construct(string $string, int $int, float $float, array $array)
            {
                $this->string = $string;
                $this->int = $int;
                $this->float = $float;
                $this->array = $array;
            }

            public function getString(): string
            {
                return $this->string;
            }

            public function getInt(): int
            {
                return $this->int;
            }

            public function getFloat(): float
            {
                return $this->float;
            }

            public function getArray(): array
            {
                return $this->array;
            }
        };
    }
}
