<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Middleware;

use Yiisoft\DataResponse\Formatter\HtmlDataResponseFormatter;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\Formatter\XmlDataResponseFormatter;
use Yiisoft\DataResponse\Middleware\FormatDataResponse;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsHtml;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsJson;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsXml;
use Yiisoft\DataResponse\Tests\TestCase;

final class FormatDataResponseTest extends TestCase
{
    public function testCorrectProcess(): void
    {
        $dataResponse = $this->createDataResponse(['test' => 'test']);
        $result = (new FormatDataResponse(new JsonDataResponseFormatter()))->process(
            $this->createRequest(),
            $this->createRequestHandler($dataResponse)
        );

        $result->getBody()->rewind();

        $this->assertSame('{"test":"test"}', $result->getBody()->getContents());
        $this->assertSame(['application/json; charset=UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testCorrectProcessWithResponseAsJson(): void
    {
        $dataResponse = $this->createDataResponse(['test' => 'test']);

        $result = (new FormatDataResponseAsJson(new JsonDataResponseFormatter()))->process(
            $this->createRequest(),
            $this->createRequestHandler($dataResponse)
        );

        $result->getBody()->rewind();

        $this->assertSame('{"test":"test"}', $result->getBody()->getContents());
        $this->assertSame(['application/json; charset=UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testCorrectProcessWithResponseAsHtml(): void
    {
        $dataResponse = $this->createDataResponse('test');

        $result = (new FormatDataResponseAsHtml(new HtmlDataResponseFormatter()))->process(
            $this->createRequest(),
            $this->createRequestHandler($dataResponse)
        );

        $result->getBody()->rewind();

        $this->assertSame('test', $result->getBody()->getContents());
        $this->assertSame(['text/html; charset=UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testCorrectProcessWithResponseAsXml(): void
    {
        $dataResponse = $this->createDataResponse(['test' => 'test']);

        $result = (new FormatDataResponseAsXml(new XmlDataResponseFormatter()))->process(
            $this->createRequest(),
            $this->createRequestHandler($dataResponse)
        );

        $result->getBody()->rewind();

        $this->assertSame($this->xml('<response><test>test</test></response>'), $result->getBody()->getContents());
        $this->assertSame(['application/xml; charset=UTF-8'], $result->getHeader('Content-Type'));
    }
}
