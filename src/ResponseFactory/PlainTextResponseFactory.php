<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\PlainTextFormatter;

/**
 * Factory that creates responses with plain text formatted {@see DataStream} body and appropriate response headers.
 */
final class PlainTextResponseFactory extends AbstractFormattedResponseFactory
{
    /**
     * @param ResponseFactoryInterface $responseFactory The PSR-17 response factory.
     * @param PlainTextFormatter $formatter The plain text formatter to use.
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        PlainTextFormatter $formatter,
    ) {
        parent::__construct($responseFactory, $formatter);
    }
}
