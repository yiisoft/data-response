<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\HtmlFormatter;

/**
 * Middleware that formats {@see DataStream} responses as HTML and sets appropriate response headers.
 */
final class HtmlDataResponseMiddleware extends AbstractDataResponseMiddleware
{
    /**
     * @param HtmlFormatter $formatter The HTML formatter to use.
     */
    public function __construct(HtmlFormatter $formatter = new HtmlFormatter())
    {
        parent::__construct($formatter);
    }
}
