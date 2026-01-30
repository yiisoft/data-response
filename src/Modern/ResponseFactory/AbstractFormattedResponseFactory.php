<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\Modern\DataStream\DataStream;
use Yiisoft\DataResponse\Modern\Formatter\FormatterInterface;
use Yiisoft\Http\Status;

abstract class AbstractFormattedResponseFactory implements DataResponseFactoryInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly FormatterInterface $formatter,
    ) {}

    final public function createResponse(
        mixed $data = null,
        int $code = Status::OK,
        string $reasonPhrase = '',
    ): ResponseInterface {
        $body = new DataStream($data, $this->formatter);
        $response = $this->responseFactory
            ->createResponse($code, $reasonPhrase)
            ->withBody($body);
        return $this->formatter->formatResponse($response);
    }
}
