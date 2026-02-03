<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\ResponseFactory;

use HttpSoft\Message\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\JsonFormatter;
use Yiisoft\DataResponse\ResponseFactory\DataResponseFactory;
use Yiisoft\Http\Status;

final class DataResponseFactoryTest extends TestCase
{
    public function testCreateResponseWithDefaults(): void
    {
        $factory = new DataResponseFactory(new ResponseFactory());

        $response = $factory->createResponse();

        $this->assertInstanceOf(DataStream::class, $response->getBody());
        $this->assertSame(Status::OK, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
    }

    public function testCreateResponseWithData(): void
    {
        $factory = new DataResponseFactory(new ResponseFactory());

        $response = $factory->createResponse('hello');

        $body = $response->getBody();
        $this->assertInstanceOf(DataStream::class, $body);
        $this->assertSame('hello', (string) $body);
    }

    public function testCreateResponseWithCustomStatusCode(): void
    {
        $factory = new DataResponseFactory(new ResponseFactory());

        $response = $factory->createResponse(null, Status::CREATED);

        $this->assertSame(Status::CREATED, $response->getStatusCode());
    }

    public function testCreateResponseWithReasonPhrase(): void
    {
        $factory = new DataResponseFactory(new ResponseFactory());

        $response = $factory->createResponse(null, Status::BAD_REQUEST, 'Custom Reason');

        $this->assertSame(Status::BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('Custom Reason', $response->getReasonPhrase());
    }

    public function testCreateResponseWithCustomFallbackFormatter(): void
    {
        $factory = new DataResponseFactory(new ResponseFactory(), new JsonFormatter());

        $response = $factory->createResponse(['key' => 'value']);

        $this->assertSame('{"key":"value"}', (string) $response->getBody());
    }
}
