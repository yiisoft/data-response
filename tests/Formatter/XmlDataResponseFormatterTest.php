<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Formatter;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\Formatter\XmlDataResponseFormatter;
use Yiisoft\DataResponse\DataResponse;

class XmlDataResponseFormatterTest extends TestCase
{
    public function testCorrectFormat(): void
    {
        $dataResponse = $this->createResponse('test');
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response>test</response>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testWithEncoding(): void
    {
        $dataResponse = $this->createResponse('test');
        $result = (new XmlDataResponseFormatter())->withEncoding('ISO-8859-1')->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<response>test</response>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; ISO-8859-1'], $result->getHeader('Content-Type'));
    }

    public function testWithVersion(): void
    {
        $dataResponse = $this->createResponse('test');
        $result = (new XmlDataResponseFormatter())->withVersion('1.1')->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.1\" encoding=\"UTF-8\"?>\n<response>test</response>\n",
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
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<exampleRootTag>test</exampleRootTag>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testItemTagWhenNameIsEmptyOrInvalid(): void
    {
        $data = [
            'test',
            'validName' => 'test',
            '1_invalidName' => 'test'
        ];
        $dataResponse = $this->createResponse($data);
        $result = (new XmlDataResponseFormatter())->withItemTag('customItemTag')->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response><customItemTag>test</customItemTag><validName>test</validName><customItemTag>test</customItemTag></response>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testWithObjectTags(): void
    {
        $data = new \stdClass();
        $data->attribute = 'test';

        $dataResponse = $this->createResponse($data);
        $result = (new XmlDataResponseFormatter())->withUseObjectTags(true)->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response><stdClass><attribute>test</attribute></stdClass></response>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testWithoutObjectTags(): void
    {
        $data = new \stdClass();
        $data->attribute = 'test';

        $dataResponse = $this->createResponse($data);
        $result = (new XmlDataResponseFormatter())->withUseObjectTags(false)->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response><attribute>test</attribute></response>\n",
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
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response>test</response>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['text/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testWithUseTraversable(): void
    {
        $data = new \ArrayObject(
            [
                'test',
                'test1'
            ]
        );

        $dataResponse = $this->createResponse($data);
        $result = (new XmlDataResponseFormatter())->withUseTraversableAsArray(false)->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response><ArrayObject><item>test</item><item>test1</item></ArrayObject></response>\n",
            $result->getBody()->getContents()
        );
    }

    public function testScalarValues(): void
    {
        $dataResponse = $this->createResponse([true, false, 100.2]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response><item>true</item><item>false</item><item>100.2</item></response>\n",
            $result->getBody()->getContents()
        );
    }

    public function testObjectValues(): void
    {
        $dataResponse = $this->createResponse([100 => new \stdClass()]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response><stdClass/></response>\n",
            $result->getBody()->getContents()
        );
    }

    public function testArrayValues(): void
    {
        $dataResponse = $this->createResponse([[100 => new \stdClass()]]);
        $result = (new XmlDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response><item><stdClass/></item></response>\n",
            $result->getBody()->getContents()
        );
    }

    public function testWithEmptyRootTag(): void
    {
        $dataResponse = $this->createResponse(['test' => 1]);
        $result = (new XmlDataResponseFormatter())->withRootTag('')->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<test>1</test>\n",
            $result->getBody()->getContents()
        );
    }

    private function createResponse($data): DataResponse
    {
        return (new DataResponseFactory(new Psr17Factory()))->createResponse($data);
    }
}
