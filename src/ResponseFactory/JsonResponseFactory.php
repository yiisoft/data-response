<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\JsonFormatter;

/**
 * Factory that creates responses with JSON-formatted {@see DataStream} body and appropriate response headers.
 */
final class JsonResponseFactory extends AbstractFormattedResponseFactory
{
    /**
     * @param ResponseFactoryInterface $responseFactory The PSR-17 response factory.
     * @param JsonFormatter $formatter The JSON formatter to use.
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        JsonFormatter $formatter,
    ) {
        parent::__construct($responseFactory, $formatter);
    }
}
