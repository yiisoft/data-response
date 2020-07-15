<?php

namespace Yiisoft\Yii\Web\Tests\Data\Formatter;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\Formatter\XmlDataResponseFormatter;
use Yiisoft\DataResponse\DataResponse;

class XmlDataResponseFormatterTest extends TestCase
{
    public function testFormatter(): void
    {
        $factory = new Psr17Factory();
        $dataResponse = new DataResponse('test', 200, '', $factory);
        $result = (new XmlDataResponseFormatter())
            ->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response>test</response>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testFormatterEncoding(): void
    {
        $factory = new Psr17Factory();
        $dataResponse = new DataResponse('test', 200, '', $factory);
        $result = (new XmlDataResponseFormatter())
            ->withEncoding('ISO-8859-1')
            ->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<response>test</response>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; ISO-8859-1'], $result->getHeader('Content-Type'));
    }

    public function testFormatterVersion(): void
    {
        $factory = new Psr17Factory();
        $dataResponse = new DataResponse('test', 200, '', $factory);
        $result = (new XmlDataResponseFormatter())
            ->withVersion('1.1')
            ->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.1\" encoding=\"UTF-8\"?>\n<response>test</response>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testFormatterRootTag(): void
    {
        $factory = new Psr17Factory();
        $dataResponse = new DataResponse('test', 200, '', $factory);
        $result = (new XmlDataResponseFormatter())
            ->withRootTag('exampleRootTag')
            ->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<exampleRootTag>test</exampleRootTag>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testFormatterItemTagWhenNameIsEmptyOrInvalid(): void
    {
        $factory = new Psr17Factory();
        $data = [
            'test',
            'validName' => 'test',
            '1_invalidName' => 'test'
        ];
        $dataResponse = new DataResponse($data, 200, '', $factory);
        $result = (new XmlDataResponseFormatter())
            ->withItemTag('customItemTag')
            ->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response><customItemTag>test</customItemTag><validName>test</validName><customItemTag>test</customItemTag></response>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testFormatterWithObjectTags(): void
    {
        $data = new \stdClass();
        $data->attribute = 'test';

        $factory = new Psr17Factory();
        $dataResponse = new DataResponse($data, 200, '', $factory);
        $result = (new XmlDataResponseFormatter())
            ->withUseObjectTags(true)
            ->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response><stdClass><attribute>test</attribute></stdClass></response>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testFormatterWithoutObjectTags(): void
    {
        $data = new \stdClass();
        $data->attribute = 'test';

        $factory = new Psr17Factory();
        $dataResponse = new DataResponse($data, 200, '', $factory);
        $result = (new XmlDataResponseFormatter())
            ->withUseObjectTags(false)
            ->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response><attribute>test</attribute></response>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testFormatterWithContentType(): void
    {
        $factory = new Psr17Factory();
        $dataResponse = new DataResponse('test', 200, '', $factory);
        $result = (new XmlDataResponseFormatter())
            ->withContentType('text/xml')
            ->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response>test</response>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['text/xml; UTF-8'], $result->getHeader('Content-Type'));
    }
}
