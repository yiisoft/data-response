<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Middleware;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\Formatter\HtmlDataResponseFormatter;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\Formatter\XmlDataResponseFormatter;
use Yiisoft\DataResponse\Middleware\FormatDataResponse;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsHtml;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsJson;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsXml;

class FormatDataResponseTest extends TestCase
{
    public function testCorrectProcess(): void
    {
        $dataResponse = $this->createResponse(['test' => 'test']);
        $result = (new FormatDataResponse(new JsonDataResponseFormatter()))->process(
            $this->createRequest(),
            $this->getRequestHandler($dataResponse)
        );

        $result->getBody()->rewind();

        $this->assertSame('{"test":"test"}', $result->getBody()->getContents());
        $this->assertSame(['application/json'], $result->getHeader('Content-Type'));
    }

    public function testCorrectProcessWithResponseAsJson(): void
    {
        $dataResponse = $this->createResponse(['test' => 'test']);
        $result = (new FormatDataResponseAsJson(new JsonDataResponseFormatter()))->process(
            $this->createRequest(),
            $this->getRequestHandler($dataResponse)
        );

        $result->getBody()->rewind();

        $this->assertSame('{"test":"test"}', $result->getBody()->getContents());
        $this->assertSame(['application/json'], $result->getHeader('Content-Type'));
    }

    public function testCorrectProcessWithResponseAsHtml(): void
    {
        $dataResponse = $this->createResponse('test');
        $result = (new FormatDataResponseAsHtml(new HtmlDataResponseFormatter()))->process(
            $this->createRequest(),
            $this->getRequestHandler($dataResponse)
        );

        $result->getBody()->rewind();

        $this->assertSame('test', $result->getBody()->getContents());
        $this->assertSame(['text/html; charset=UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testCorrectProcessWithResponseAsXml(): void
    {
        $dataResponse = $this->createResponse(['test' => 'test']);
        $result = (new FormatDataResponseAsXml(new XmlDataResponseFormatter()))->process(
            $this->createRequest(),
            $this->getRequestHandler($dataResponse)
        );

        $result->getBody()->rewind();

        $this->assertSame(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<response><test>test</test></response>\n",
            $result->getBody()->getContents()
        );
        $this->assertSame(['application/xml; UTF-8'], $result->getHeader('Content-Type'));
    }

    private function getRequestHandler(ResponseInterface $response): RequestHandlerInterface
    {
        return new class($response) implements RequestHandlerInterface {
            private ResponseInterface $response;

            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }

    private function createResponse($data): DataResponse
    {
        return (new DataResponseFactory(new Psr17Factory()))->createResponse($data);
    }

    private function createRequest(): ServerRequest
    {
        return new ServerRequest('GET', '/test');
    }
}
