<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests;

use HttpSoft\Message\ResponseFactory;
use Yiisoft\DataResponse\NotAcceptableRequestHandler;
use Yiisoft\Http\Status;

final class NotAcceptableRequestHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $handler = new NotAcceptableRequestHandler(new ResponseFactory());

        $response = $handler->handle($this->createRequest());

        $this->assertSame(Status::NOT_ACCEPTABLE, $response->getStatusCode());
    }
}
