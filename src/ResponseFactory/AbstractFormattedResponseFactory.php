<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\FormatterInterface;
use Yiisoft\Http\Status;

/**
 * Abstract factory class that creates responses with pre-formatted {@see DataStream} body
 * and appropriate response headers.
 *
 * The formatter is applied immediately when creating the response.
 */
abstract class AbstractFormattedResponseFactory implements DataResponseFactoryInterface
{
    /**
     * @param ResponseFactoryInterface $responseFactory The PSR-17 response factory.
     * @param FormatterInterface $formatter The formatter to apply to the response.
     */
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
