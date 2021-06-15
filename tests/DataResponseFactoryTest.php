<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\Http\Status;

final class DataResponseFactoryTest extends TestCase
{
    public function testCreateResponseWithDefaultParams(): void
    {
        $response = $this->createDataResponseFactory()->createResponse();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(DataResponse::class, $response);

        $this->assertNull($response->getData());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
    }

    public function testCreateResponseWithCustomParams(): void
    {
        $response = $this->createDataResponseFactory()->createResponse(['key' => 'value'], Status::BAD_REQUEST, 'reason');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(DataResponse::class, $response);

        $this->assertSame(['key' => 'value'], $response->getData());
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('reason', $response->getReasonPhrase());
    }
}
