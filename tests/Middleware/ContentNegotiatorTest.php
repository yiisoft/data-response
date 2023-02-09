<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Middleware;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use StdClass;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\Formatter\HtmlDataResponseFormatter;
use Yiisoft\DataResponse\Formatter\XmlDataResponseFormatter;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\Middleware\ContentNegotiator;
use Yiisoft\DataResponse\Tests\TestCase;
use Yiisoft\Http\Header;

final class ContentNegotiatorTest extends TestCase
{
    public function testAcceptHtml(): void
    {
        $middleware = new ContentNegotiator($this->getContentFormatters());
        $response = $this->process($middleware, 'text/html', '<div>Hello</div>');
        $content = $response
            ->getBody()
            ->getContents();

        $this->assertInstanceOf(DataResponse::class, $response);
        $this->assertTrue($response->hasResponseFormatter());
        $this->assertSame('<div>Hello</div>', $content);
        $this->assertSame('text/html; charset=UTF-8', $response->getHeader(Header::CONTENT_TYPE)[0]);
    }

    public function testAcceptXml(): void
    {
        $middleware = new ContentNegotiator($this->getContentFormatters());
        $response = $this->process($middleware, 'application/xml', 'Hello');
        $content = $response
            ->getBody()
            ->getContents();

        $this->assertInstanceOf(DataResponse::class, $response);
        $this->assertTrue($response->hasResponseFormatter());
        $this->assertSame($this->xml('<response>Hello</response>'), $content);
        $this->assertSame('application/xml; charset=UTF-8', $response->getHeader(Header::CONTENT_TYPE)[0]);
    }

    public function testAcceptJson(): void
    {
        $middleware = new ContentNegotiator($this->getContentFormatters());
        $response = $this->process($middleware, 'application/json', ['test' => 'Hello']);
        $content = $response
            ->getBody()
            ->getContents();

        $this->assertInstanceOf(DataResponse::class, $response);
        $this->assertTrue($response->hasResponseFormatter());
        $this->assertSame('{"test":"Hello"}', $content);
        $this->assertSame('application/json; charset=UTF-8', $response->getHeader(Header::CONTENT_TYPE)[0]);
    }

    public function testNotExistsFormatter(): void
    {
        $middleware = new ContentNegotiator($this->getContentFormatters());
        $response = $this->process($middleware, 'text/plain', 'Hello World!');
        $content = $response
            ->getBody()
            ->getContents();

        $this->assertInstanceOf(DataResponse::class, $response);
        $this->assertFalse($response->hasResponseFormatter());
        $this->assertSame('Hello World!', $content);
    }

    public function testWrongContentFormattersInConstructor(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Invalid formatter. A "Yiisoft\DataResponse\DataResponseFormatterInterface"'
            . ' instance is expected, "stdClass" is received.'
        );
        new ContentNegotiator([
            'text/html' => new HtmlDataResponseFormatter(),
            'application/xml' => new StdClass(),
        ]);
    }

    public function testWrongContentFormattersInSetter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid formatter content type. A string is expected, "integer" is received.');
        $middleware = new ContentNegotiator($this->getContentFormatters());
        $middleware->withContentFormatters([
            'text/html' => new HtmlDataResponseFormatter(),
            0 => new JsonDataResponseFormatter(),
        ]);
    }

    public function testImmutability(): void
    {
        $middleware = new ContentNegotiator([]);
        $this->assertNotSame($middleware, $middleware->withContentFormatters([]));
    }

    /**
     * @param ContentNegotiator $middleware
     * @param string $headerAcceptValue
     * @param mixed $data
     *
     * @return ResponseInterface
     */
    private function process(ContentNegotiator $middleware, string $headerAcceptValue, $data): ResponseInterface
    {
        $response = $middleware->process(
            $this
                ->createRequest()
                ->withHeader(Header::ACCEPT, $headerAcceptValue),
            $this->createRequestHandler($this
                ->createDataResponseFactory()
                ->createResponse($data)),
        );

        $response
            ->getBody()
            ->rewind();
        return $response;
    }

    private function getContentFormatters(): array
    {
        return [
            'text/html' => new HtmlDataResponseFormatter(),
            'application/xml' => new XmlDataResponseFormatter(),
            'application/json' => new JsonDataResponseFormatter(),
        ];
    }
}
