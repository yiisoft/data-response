<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\Http\Status;

/**
 * Factory that creates responses with {@see DataStream} body without applying a formatter.
 *
 * The formatter should be applied later using middleware.
 */
final class DataResponseFactory implements DataResponseFactoryInterface
{
    /**
     * @param ResponseFactoryInterface $responseFactory The PSR-17 response factory.
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

    public function createResponse(
        mixed $data = null,
        int $code = Status::OK,
        string $reasonPhrase = '',
    ): ResponseInterface {
        $body = new DataStream($data);
        return $this->responseFactory
            ->createResponse($code, $reasonPhrase)
            ->withBody($body);
    }
}
