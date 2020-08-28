<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\Http\Status;

class DataResponseFactoryTest extends TestCase
{
    public function testCreateResponseWithDefaultParams(): void
    {
        $response = $this->createFactory()->createResponse();

        $this->assertNull($response->getData());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty($response->getReasonPhrase());
    }

    public function testCreateResponseWithCustomParams(): void
    {
        $response = $this->createFactory()->createResponse(['key' => 'value'], Status::BAD_REQUEST, 'reason');
        $this->assertEquals(['key' => 'value'], $response->getData());
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('reason', $response->getReasonPhrase());
    }

    private function createFactory(): DataResponseFactory
    {
        return new DataResponseFactory(new Psr17Factory());
    }
}
