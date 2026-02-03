<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\ResponseFactory;

use HttpSoft\Message\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\Formatter\JsonFormatter;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;

final class JsonResponseFactoryTest extends TestCase
{
    public function testCreateResponse(): void
    {
        $factory = new JsonResponseFactory(new ResponseFactory(), new JsonFormatter());

        $response = $factory->createResponse(['key' => 'value']);

        $this->assertSame('{"key":"value"}', (string) $response->getBody());
        $this->assertSame('application/json; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
        $this->assertSame(Status::OK, $response->getStatusCode());
    }

    public function testCreateResponseWithCustomStatusCode(): void
    {
        $factory = new JsonResponseFactory(new ResponseFactory(), new JsonFormatter());

        $response = $factory->createResponse(['error' => 'not found'], Status::NOT_FOUND, 'Not Found');

        $this->assertSame(Status::NOT_FOUND, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());
    }
}
