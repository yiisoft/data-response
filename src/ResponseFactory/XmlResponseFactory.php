<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\XmlFormatter;

/**
 * Factory that creates responses with XML-formatted {@see DataStream} body and appropriate response headers.
 */
final class XmlResponseFactory extends AbstractFormattedResponseFactory
{
    /**
     * @param ResponseFactoryInterface $responseFactory The PSR-17 response factory.
     * @param XmlFormatter $formatter The XML formatter to use.
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        XmlFormatter $formatter,
    ) {
        parent::__construct($responseFactory, $formatter);
    }
}
