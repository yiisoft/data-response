<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\FormatterInterface;
use Yiisoft\DataResponse\Formatter\HtmlFormatter;
use Yiisoft\Http\Status;

/**
 * Factory that creates responses with {@see DataStream} body without applying a formatter.
 *
 * The formatter can be applied later using middleware. If no formatter is applied before reading the response body,
 * the fallback formatter is used.
 */
final class DataResponseFactory implements DataResponseFactoryInterface
{
    /**
     * @param ResponseFactoryInterface $responseFactory The PSR-17 response factory.
     * @param FormatterInterface $fallbackFormatter The formatter to use when no formatter is applied.
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly FormatterInterface $fallbackFormatter = new HtmlFormatter(),
    ) {}

    public function createResponse(
        mixed $data = null,
        int $code = Status::OK,
        string $reasonPhrase = '',
    ): ResponseInterface {
        $body = new DataStream($data, fallbackFormatter: $this->fallbackFormatter);
        return $this->responseFactory
            ->createResponse($code, $reasonPhrase)
            ->withBody($body);
    }
}
