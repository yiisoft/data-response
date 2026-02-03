<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\ResponseFactory;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Yiisoft\DataResponse\Formatter\JsonFormatter;
use Yiisoft\DataResponse\Formatter\PlainTextFormatter;
use Yiisoft\DataResponse\Formatter\XmlFormatter;
use Yiisoft\DataResponse\ResponseFactory\ContentNegotiatorResponseFactory;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\DataResponse\ResponseFactory\PlainTextResponseFactory;
use Yiisoft\DataResponse\ResponseFactory\XmlResponseFactory;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;

final class ContentNegotiatorResponseFactoryTest extends TestCase
{
    public function testCreateResponseWithMatchingAcceptHeader(): void
    {
        $responseFactory = new ResponseFactory();
        $factory = new ContentNegotiatorResponseFactory(
            [
                'application/json' => new JsonResponseFactory($responseFactory, new JsonFormatter()),
                'application/xml' => new XmlResponseFactory($responseFactory, new XmlFormatter()),
            ],
            new PlainTextResponseFactory($responseFactory, new PlainTextFormatter()),
        );

        $request = (new ServerRequest())->withHeader(Header::ACCEPT, 'application/json');
        $response = $factory->createResponse($request, ['key' => 'value']);

        $this->assertSame('{"key":"value"}', (string) $response->getBody());
        $this->assertSame('application/json; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testCreateResponseWithMultipleAcceptHeaders(): void
    {
        $responseFactory = new ResponseFactory();
        $factory = new ContentNegotiatorResponseFactory(
            [
                'application/json' => new JsonResponseFactory($responseFactory, new JsonFormatter()),
                'application/xml' => new XmlResponseFactory($responseFactory, new XmlFormatter()),
            ],
            new PlainTextResponseFactory($responseFactory, new PlainTextFormatter()),
        );

        $request = (new ServerRequest())->withHeader(
            Header::ACCEPT,
            'text/html, application/xml;q=0.9, application/json;q=0.8',
        );
        $response = $factory->createResponse($request, ['key' => 'value']);

        $this->assertSame('application/xml; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testCreateResponseWithNoMatchingAcceptHeaderUsesFallback(): void
    {
        $responseFactory = new ResponseFactory();
        $factory = new ContentNegotiatorResponseFactory(
            [
                'application/json' => new JsonResponseFactory($responseFactory, new JsonFormatter()),
            ],
            new PlainTextResponseFactory($responseFactory, new PlainTextFormatter()),
        );

        $request = (new ServerRequest())->withHeader(Header::ACCEPT, 'text/html');
        $response = $factory->createResponse($request, 'test content');

        $this->assertSame('test content', (string) $response->getBody());
        $this->assertSame('text/plain; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
    }

    public function testCreateResponseWithCustomStatusCode(): void
    {
        $responseFactory = new ResponseFactory();
        $factory = new ContentNegotiatorResponseFactory(
            [
                'application/json' => new JsonResponseFactory($responseFactory, new JsonFormatter()),
            ],
            new PlainTextResponseFactory($responseFactory, new PlainTextFormatter()),
        );

        $request = (new ServerRequest())->withHeader(Header::ACCEPT, 'application/json');
        $response = $factory->createResponse($request, ['error' => 'not found'], Status::NOT_FOUND, 'Not Found');

        $this->assertSame(Status::NOT_FOUND, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());
    }

    public function testConstructorWithInvalidContentType(): void
    {
        $responseFactory = new ResponseFactory();
        $factories = [new JsonResponseFactory($responseFactory, new JsonFormatter())];
        $fallbackFactory = new PlainTextResponseFactory($responseFactory, new PlainTextFormatter());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid formatter content type. A string is expected, "integer" is received.');
        new ContentNegotiatorResponseFactory($factories, $fallbackFactory);
    }

    public function testConstructorWithInvalidFactory(): void
    {
        $responseFactory = new ResponseFactory();
        $factories = [
            'application/json' => new stdClass(),
        ];
        $fallbackFactory = new PlainTextResponseFactory($responseFactory, new PlainTextFormatter());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Invalid formatter. A "Yiisoft\DataResponse\ResponseFactory\DataResponseFactoryInterface" instance is expected, "stdClass" is received.',
        );
        new ContentNegotiatorResponseFactory($factories, $fallbackFactory);
    }
}
