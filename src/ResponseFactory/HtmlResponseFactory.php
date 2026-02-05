<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\HtmlFormatter;

/**
 * Factory that creates responses with HTML-formatted {@see DataStream} body and appropriate response headers.
 */
final class HtmlResponseFactory extends AbstractFormattedResponseFactory
{
    /**
     * @param ResponseFactoryInterface $responseFactory The PSR-17 response factory.
     * @param HtmlFormatter $formatter The HTML formatter to use.
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        HtmlFormatter $formatter,
    ) {
        parent::__construct($responseFactory, $formatter);
    }
}
