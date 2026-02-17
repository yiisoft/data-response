<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\ResponseFactory;

use HttpSoft\Message\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\Formatter\HtmlFormatter;
use Yiisoft\DataResponse\ResponseFactory\HtmlResponseFactory;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;

final class HtmlResponseFactoryTest extends TestCase
{
    public function testCreateResponse(): void
    {
        $factory = new HtmlResponseFactory(new ResponseFactory(), new HtmlFormatter());

        $response = $factory->createResponse('test content');

        $this->assertSame('test content', (string) $response->getBody());
        $this->assertSame('text/html; charset=UTF-8', $response->getHeaderLine(Header::CONTENT_TYPE));
        $this->assertSame(Status::OK, $response->getStatusCode());
    }

    public function testCreateResponseWithCustomStatusCode(): void
    {
        $factory = new HtmlResponseFactory(new ResponseFactory(), new HtmlFormatter());

        $response = $factory->createResponse('error', Status::NOT_FOUND, 'Not Found');

        $this->assertSame(Status::NOT_FOUND, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());
    }
}
