<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Formatter;

use ArrayObject;
use stdClass;
use Yiisoft\DataResponse\Formatter\XmlDataInterface;
use Yiisoft\DataResponse\Formatter\XmlDataResponseFormatter;
use Yiisoft\DataResponse\Tests\TestCase;
use Yiisoft\Http\Header;

final class XmlDataResponseFormatterTest extends TestCase
{
    public function testCorrectFormat(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml('<response>test</response>'),
            $result
                ->getBody()
                ->getContents()
        );
        $this->assertSame(['application/xml; charset=UTF-8'], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testWithEncoding(): void
    {
        $encoding = 'ISO-8859-1';
        $dataResponse = $this->createDataResponse('test');
        $result = (new XmlDataResponseFormatter())
            ->withEncoding($encoding)
            ->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml('<response>test</response>', '1.0', $encoding),
            $result
                ->getBody()
                ->getContents()
        );
        $this->assertSame(["application/xml; charset={$encoding}"], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testWithVersion(): void
    {
        $version = '1.1';
        $dataResponse = $this->createDataResponse('test');
        $result = (new XmlDataResponseFormatter())
            ->withVersion($version)
            ->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml('<response>test</response>', $version),
            $result
                ->getBody()
                ->getContents()
        );
        $this->assertSame(['application/xml; charset=UTF-8'], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testWithRootTag(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $result = (new XmlDataResponseFormatter())
            ->withRootTag('exampleRootTag')
            ->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml('<exampleRootTag>test</exampleRootTag>'),
            $result
                ->getBody()
                ->getContents()
        );
        $this->assertSame(['application/xml; charset=UTF-8'], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testWithoutRootTag(): void
    {
        $dataResponse = $this->createDataResponse(['tag' => 'value']);
        $result = (new XmlDataResponseFormatter())
            ->withRootTag('')
            ->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml('<tag>value</tag>'),
            $result
                ->getBody()
                ->getContents()
        );
        $this->assertSame(['application/xml; charset=UTF-8'], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testWithEmptyRootTag(): void
    {
        $dataResponse = $this->createDataResponse('');
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml('<response/>'),
            $result
                ->getBody()
                ->getContents()
        );
        $this->assertSame(['application/xml; charset=UTF-8'], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testWithEmptyResponse(): void
    {
        $dataResponse = $this->createDataResponse(null);
        $result = (new XmlDataResponseFormatter())
            ->withRootTag('')
            ->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            '',
            $result
                ->getBody()
                ->getContents()
        );
        $this->assertSame(['application/xml; charset=UTF-8'], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testWithContentType(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $result = (new XmlDataResponseFormatter())
            ->withContentType('text/xml')
            ->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml('<response>test</response>'),
            $result
                ->getBody()
                ->getContents()
        );
        $this->assertSame(['text/xml; charset=UTF-8'], $result->getHeader(Header::CONTENT_TYPE));
    }

    public function testScalarValues(): void
    {
        $dataResponse = $this->createDataResponse([true, false, 100.2]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <item>true</item>
                        <item>false</item>
                        <item>100.2</item>
                    </response>
                EOF
            ),
            $result
                ->getBody()
                ->getContents()
        );
    }

    public function testArrayValues(): void
    {
        $dataResponse = $this->createDataResponse([
            [100 => [], '200' => '', 300 => null],
            [1, 1.1, 'foo' => 'bar', true, false],
        ]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <item>
                            <item/>
                            <item/>
                            <item/>
                        </item>
                        <item>
                            <item>1</item>
                            <item>1.1</item>
                            <foo>bar</foo>
                            <item>true</item>
                            <item>false</item>
                        </item>
                    </response>
                EOF
            ),
            $result
                ->getBody()
                ->getContents()
        );
    }

    public function testTraversableValue(): void
    {
        $dataResponse = $this->createDataResponse(['array-value' => new ArrayObject(['test', null])]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <array-value>
                            <item>test</item>
                            <item/>
                        </array-value>
                    </response>
                EOF
            ),
            $result
                ->getBody()
                ->getContents()
        );
    }

    public function testTraversableValues(): void
    {
        $dataResponse = $this->createDataResponse([
            new ArrayObject(['foo' => 'bar', null]),
            new ArrayObject([true, false]),
            new ArrayObject(['array' => [
                new ArrayObject(['test', null]),
                new ArrayObject([true, false]),
            ]]),
        ]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <item>
                            <foo>bar</foo>
                            <item/>
                        </item>
                        <item>
                            <item>true</item>
                            <item>false</item>
                        </item>
                        <item>
                            <array>
                                <item>
                                    <item>test</item>
                                    <item/>
                                </item>
                                <item>
                                    <item>true</item>
                                    <item>false</item>
                                </item>
                            </array>
                        </item>
                    </response>
                EOF
            ),
            $result
                ->getBody()
                ->getContents()
        );
    }

    public function testPriorityXmlDataInterfaceOverTraversable(): void
    {
        $dataResponse = $this->createDataResponse(new class (['foo']) extends ArrayObject implements XmlDataInterface {
            public function xmlTagName(): string
            {
                return 'xml-data';
            }

            public function xmlTagAttributes(): array
            {
                return ['attribute' => 'test'];
            }

            public function xmlData(): array
            {
                return ['foo' => 'bar'];
            }
        });
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <xml-data attribute="test">
                            <foo>bar</foo>
                        </xml-data>
                    </response>
                EOF
            ),
            $result
                ->getBody()
                ->getContents()
        );
    }

    public function testPriorityXmlDataInterfaceOverTraversableInArray(): void
    {
        $dataResponse = $this->createDataResponse([new class (['foo']) extends ArrayObject implements XmlDataInterface {
            public function xmlTagName(): string
            {
                return 'xml-data';
            }

            public function xmlTagAttributes(): array
            {
                return ['attribute' => 'test'];
            }

            public function xmlData(): array
            {
                return ['foo' => 'bar'];
            }
        }]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <xml-data attribute="test">
                            <foo>bar</foo>
                        </xml-data>
                    </response>
                EOF
            ),
            $result
                ->getBody()
                ->getContents()
        );
    }

    public function testEmptyObjectValues(): void
    {
        $dataResponse = $this->createDataResponse(['object' => new stdClass()]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml('<response><object/></response>'),
            $result
                ->getBody()
                ->getContents()
        );
    }

    public function testEmptyObjectValuesImplementXmlDataInterface(): void
    {
        $dataResponse = $this->createDataResponse(['object' => new class () implements XmlDataInterface {
            public function xmlTagName(): string
            {
                return 'empty';
            }

            public function xmlTagAttributes(): array
            {
                return [];
            }

            public function xmlData(): array
            {
                return [];
            }
        }]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml('<response><empty/></response>'),
            $result
                ->getBody()
                ->getContents()
        );
    }

    public function testObjectValues(): void
    {
        $object = $this->createDummyObject('foo', 99, 1.1, [1, 'foo' => 'bar']);
        $dataResponse = $this->createDataResponse($object);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <dummy-class>
                            <string>$object->string</string>
                            <int>$object->int</int>
                            <float>$object->float</float>
                            <array>
                                <item>1</item>
                                <foo>bar</foo>
                            </array>
                        </dummy-class>
                    </response>
                EOF
            ),
            $result
                ->getBody()
                ->getContents()
        );
    }

    public function testArrayObjectValues(): void
    {
        $objects = [
            $object1 = $this->createDummyObject('foo', 99, 1.1, ['foo', 1.1]),
            $object2 = $this->createDummyObject('bar', 10, 2.2, [1, 2, 3]),
            $object3 = $this->createDummyObject('baz', 0, 3.3, ['bar' => 'baz']),
        ];
        $dataResponse = $this->createDataResponse($objects);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <dummy-class>
                            <string>$object1->string</string>
                            <int>$object1->int</int>
                            <float>$object1->float</float>
                            <array>
                                <item>foo</item>
                                <item>1.1</item>
                            </array>
                        </dummy-class>
                        <dummy-class>
                            <string>$object2->string</string>
                            <int>$object2->int</int>
                            <float>$object2->float</float>
                            <array>
                                <item>1</item>
                                <item>2</item>
                                <item>3</item>
                            </array>
                        </dummy-class>
                        <dummy-class>
                            <string>$object3->string</string>
                            <int>$object3->int</int>
                            <float>$object3->float</float>
                            <array><bar>baz</bar></array>
                        </dummy-class>
                    </response>
                EOF
            ),
            $result
                ->getBody()
                ->getContents()
        );
    }

    public function testObjectValuesWithAttributes(): void
    {
        $objects = [
            $object1 = $this->createDummyObject('foo', 99, 1.1, [
                $object2 = $this->createDummyObject('bar', 10, 2.2, [1, 2, 3], ['attribute' => '25']),
                $object3 = $this->createDummyObject('baz', 0, 3.3, ['bar' => 'baz'], ['attribute' => '']),
            ], ['attribute' => '22']),
        ];
        $dataResponse = $this->createDataResponse($objects);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <dummy-class attribute="22">
                            <string>$object1->string</string>
                            <int>$object1->int</int>
                            <float>$object1->float</float>
                            <array>
                                <dummy-class attribute="25">
                                    <string>$object2->string</string>
                                    <int>$object2->int</int>
                                    <float>$object2->float</float>
                                    <array>
                                        <item>1</item>
                                        <item>2</item>
                                        <item>3</item>
                                    </array>
                                </dummy-class>
                                <dummy-class attribute="">
                                    <string>$object3->string</string>
                                    <int>$object3->int</int>
                                    <float>$object3->float</float>
                                    <array><bar>baz</bar></array>
                                </dummy-class>
                            </array>
                        </dummy-class>
                    </response>
                EOF
            ),
            $result
                ->getBody()
                ->getContents()
        );
    }

    public function testNestedAndMixedValues(): void
    {
        $dataResponse = $this->createDataResponse([
            $object1 = $this->createDummyObject('foo', 99, 1.1, [
                $object2 = $this->createDummyObject('bar', 11, 0.1, [
                    'foo' => 'bar',
                    1.1,
                    $object3 = $this->createDummyObject('baz', 1, 2.2, [true, false]),
                ]),
            ]),
            true,
            'foo' => 'bar',
            false,
            11,
            'baz',
        ]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <dummy-class>
                            <string>$object1->string</string>
                            <int>$object1->int</int>
                            <float>$object1->float</float>
                            <array>
                                <dummy-class>
                                    <string>$object2->string</string>
                                    <int>$object2->int</int>
                                    <float>$object2->float</float>
                                    <array>
                                        <foo>bar</foo>
                                        <item>1.1</item>
                                        <dummy-class>
                                            <string>$object3->string</string>
                                            <int>$object3->int</int>
                                            <float>$object3->float</float>
                                            <array>
                                                <item>true</item>
                                                <item>false</item>
                                            </array>
                                        </dummy-class>
                                    </array>
                                </dummy-class>
                            </array>
                        </dummy-class>
                        <item>true</item>
                        <foo>bar</foo>
                        <item>false</item>
                        <item>11</item>
                        <item>baz</item>
                    </response>
                EOF
            ),
            $result
                ->getBody()
                ->getContents()
        );
    }

    public function testObjectWithPublicProperties(): void
    {
        $object = new class () {
            public int $x = 7;
            public float $y = 42;
            public string $name = 'yii';
            protected int $a = 1;
            private int $b = 2;
        };
        $dataResponse = $this->createDataResponse($object);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <item>
                            <x>7</x>
                            <y>42</y>
                            <name>yii</name>
                        </item>
                    </response>
                EOF
            ),
            $result
                ->getBody()
                ->getContents()
        );
    }

    public function testItemTagWhenNameIsEmptyOrInvalid(): void
    {
        $dataResponse = $this->createDataResponse([
            'test',
            'validName' => 'test',
            '1_invalidName' => 'test',
        ]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result
            ->getBody()
            ->rewind();

        $this->assertSame(
            $this->xml(
                <<<EOF
                    <response>
                        <item>test</item>
                        <validName>test</validName>
                        <item>test</item>
                    </response>
                EOF
            ),
            $result
                ->getBody()
                ->getContents()
        );
    }

    private function createDummyObject(string $string, int $int, float $float, array $array, array $attrs = []): object
    {
        return new class ($string, $int, $float, $array, $attrs) implements XmlDataInterface {
            public function __construct(
                public string $string,
                public int $int,
                public float $float,
                public array $array,
                public array $attrs = [],
            ) {
            }

            public function xmlTagName(): string
            {
                return 'dummy-class';
            }

            public function xmlTagAttributes(): array
            {
                return $this->attrs;
            }

            public function xmlData(): array
            {
                return [
                    'string' => $this->string,
                    'int' => $this->int,
                    'float' => $this->float,
                    'array' => $this->array,
                ];
            }
        };
    }

    public function testImmutability(): void
    {
        $formatter = new XmlDataResponseFormatter();
        $this->assertNotSame($formatter, $formatter->withContentType('text/plain'));
        $this->assertNotSame($formatter, $formatter->withEncoding('utf-8'));
        $this->assertNotSame($formatter, $formatter->withRootTag('order'));
        $this->assertNotSame($formatter, $formatter->withVersion('5.45'));
    }
}
