<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\XmlFormatter;

/**
 * Middleware that formats {@see DataStream} responses as XML and sets appropriate response headers.
 */
final class XmlDataResponseMiddleware extends AbstractDataResponseMiddleware
{
    /**
     * @param XmlFormatter $formatter The XML formatter to use.
     */
    public function __construct(XmlFormatter $formatter = new XmlFormatter())
    {
        parent::__construct($formatter);
    }
}
