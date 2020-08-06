<?php

namespace Yiisoft\DataResponse\Tests\Middleware;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\Middleware\FormatDataResponse;
use Yiisoft\DataResponse\DataResponse;

class FormatDataResponseTest extends TestCase
{
    public function testFormatter(): void
    {
        $request = new ServerRequest('GET', '/test');
        $factory = new Psr17Factory();
        $dataResponse = new DataResponse(['test' => 'test'], 200, '', $factory);
        $formatter = new FormatDataResponse(new JsonDataResponseFormatter());
        $result = $formatter->process($request, $this->getRequestHandler($dataResponse));
        $result->getBody()->rewind();

        $this->assertSame('{"test":"test"}', $result->getBody()->getContents());
        $this->assertSame(['application/json'], $result->getHeader('Content-Type'));
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
}
