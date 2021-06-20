<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use HttpSoft\Message\StreamFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;

use function preg_replace;
use function sprintf;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function createDataResponse($data, int $status = Status::OK, string $reasonPhrase = ''): DataResponse
    {
        return new DataResponse($data, $status, $reasonPhrase, new ResponseFactory(), new StreamFactory());
    }

    protected function createDataResponseWithCustomResponseFactory(ResponseFactoryInterface $factory): DataResponse
    {
        return new DataResponse(null, Status::OK, '', $factory, new StreamFactory());
    }

    protected function createDataResponseFactory(): DataResponseFactory
    {
        return new DataResponseFactory(new ResponseFactory(), new StreamFactory());
    }

    protected function createStream(string $content = ''): StreamInterface
    {
        return (new StreamFactory())->createStream($content);
    }

    protected function createRequest(string $method = Method::GET, string $uri = '/'): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest($method, $uri);
    }

    protected function createRequestHandler(ResponseInterface $response): RequestHandlerInterface
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

    protected function xml(string $data, string $version = '1.0', string $encoding = 'UTF-8'): string
    {
        $startLine = sprintf('<?xml version="%s" encoding="%s"?>', $version, $encoding);
        return $startLine . "\n" . preg_replace('/(?!item)\s(?!attribute)/', '', $data) . "\n";
    }
}
