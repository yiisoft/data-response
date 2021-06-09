<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Formatter;

use ArrayObject;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use stdClass;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\Formatter\XmlDataResponseFormatter;

use function preg_replace;
use function sprintf;

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
        $this->assertSame(['application/xml; charset=UTF-8'], $result->getHeader('Content-Type'));
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
        $this->assertSame(["application/xml; charset={$encoding}"], $result->getHeader('Content-Type'));
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
        $this->assertSame(['application/xml; charset=UTF-8'], $result->getHeader('Content-Type'));
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
        $this->assertSame(['application/xml; charset=UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testWithoutRootTag(): void
    {
        $dataResponse = $this->createResponse(['tag' => 'value']);
        $result = (new XmlDataResponseFormatter())->withRootTag('')->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml('<tag>value</tag>'),
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; charset=UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testWithEmptyRootTag(): void
    {
        $dataResponse = $this->createResponse('');
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml('<response/>'),
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; charset=UTF-8'], $result->getHeader('Content-Type'));
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
        $this->assertSame(['text/xml; charset=UTF-8'], $result->getHeader('Content-Type'));
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
            [100 => [], '200' => '', 300 => null],
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
                            <item key="300"/>
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

    public function testTraversableValue(): void
    {
        $dataResponse = $this->createResponse(new ArrayObject(['test', null]));
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <item key="0">test</item>
                        <item key="1"/>
                    </response>
                EOF
            ),
            $result->getBody()->getContents()
        );
    }

    public function testTraversableValues(): void
    {
        $dataResponse = $this->createResponse([
            new ArrayObject(['test', null]),
            new ArrayObject([true, false]),
            new ArrayObject(['array' => [
                new ArrayObject(['test', null]),
                new ArrayObject([true, false]),
            ]]),
        ]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <item key="0">
                            <item key="0">test</item>
                            <item key="1"/>
                        </item>
                        <item key="1">
                            <item key="0">true</item>
                            <item key="1">false</item>
                        </item>
                        <item key="2">
                            <array>
                                <item key="0">
                                    <item key="0">test</item>
                                    <item key="1"/>
                                </item>
                                <item key="1">
                                    <item key="0">true</item>
                                    <item key="1">false</item>
                                </item>
                            </array>
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
            $this->xml('<response><object><stdClass/></object></response>'),
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
                        <AnonymousClass>
                            <string>$object->string</string>
                            <int>$object->int</int>
                            <float>$object->float</float>
                            <array>
                                <item key="0">1</item>
                                <foo>bar</foo>
                            </array>
                        </AnonymousClass>
                    </response>
                EOF
            ),
            $result->getBody()->getContents()
        );
    }

    public function testObjectValuesWithoutUseObjectTags(): void
    {
        $object = $this->createDummyObject('foo', 99, 1.1, [1, 'foo' => 'bar']);
        $dataResponse = $this->createResponse($object);
        $result = (new XmlDataResponseFormatter())->withUseObjectTags(false)->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <item>
                            <string>$object->string</string>
                            <int>$object->int</int>
                            <float>$object->float</float>
                            <array>
                                <item key="0">1</item>
                                <foo>bar</foo>
                            </array>
                        </item>
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
                        <AnonymousClass key="0">
                            <string>$object1->string</string>
                            <int>$object1->int</int>
                            <float>$object1->float</float>
                            <array>
                                <item key="0">foo</item>
                                <item key="1">1.1</item>
                            </array>
                        </AnonymousClass>
                        <AnonymousClass key="1">
                            <string>$object2->string</string>
                            <int>$object2->int</int>
                            <float>$object2->float</float>
                            <array>
                                <item key="0">1</item>
                                <item key="1">2</item>
                                <item key="2">3</item>
                            </array>
                        </AnonymousClass>
                        <AnonymousClass key="2">
                            <string>$object3->string</string>
                            <int>$object3->int</int>
                            <float>$object3->float</float>
                            <array><bar>baz</bar></array>
                        </AnonymousClass>
                    </response>
                EOF
            ),
            $result->getBody()->getContents()
        );
    }

    public function testItemTagWhenNameIsEmptyOrInvalid(): void
    {
        $dataResponse = $this->createResponse([
            'test',
            'validName' => 'test',
            '1_invalidName' => 'test',
        ]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <item key="0">test</item>
                        <validName>test</validName>
                        <item>test</item>
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
            public string $string;
            public int $int;
            public float $float;
            public array $array;

            public function __construct(string $string, int $int, float $float, array $array)
            {
                $this->string = $string;
                $this->int = $int;
                $this->float = $float;
                $this->array = $array;
            }
        };
    }
}
