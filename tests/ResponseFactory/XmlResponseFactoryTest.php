<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\ResponseFactory;

use HttpSoft\Message\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\Formatter\XmlFormatter;
use Yiisoft\DataResponse\ResponseFactory\XmlResponseFactory;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;

final class XmlResponseFactoryTest extends TestCase
{
    public function testCreateResponse(): void
    {
        $factory = new XmlResponseFactory(new ResponseFactory(), new XmlFormatter());

        $response = $factory->createResponse(['key' => 'value']);

        $expected = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <response><key>value</key></response>

            XML;
        $this->assertSame($expected, (string) $response->getBody());
        $this->assertSame('application/xml; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
        $this->assertSame(Status::OK, $response->getStatusCode());
    }

    public function testCreateResponseWithCustomStatusCode(): void
    {
        $factory = new XmlResponseFactory(new ResponseFactory(), new XmlFormatter());

        $response = $factory->createResponse(['error' => 'not found'], Status::NOT_FOUND, 'Not Found');

        $this->assertSame(Status::NOT_FOUND, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());
    }
}
