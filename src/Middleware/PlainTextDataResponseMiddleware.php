<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\PlainTextFormatter;

/**
 * Middleware that formats {@see DataStream} responses as plain text and sets appropriate response headers.
 */
final class PlainTextDataResponseMiddleware extends AbstractDataResponseMiddleware
{
    /**
     * @param PlainTextFormatter $formatter The plain text formatter to use.
     */
    public function __construct(PlainTextFormatter $formatter = new PlainTextFormatter())
    {
        parent::__construct($formatter);
    }
}
