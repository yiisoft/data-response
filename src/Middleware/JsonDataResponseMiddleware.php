<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\JsonFormatter;

/**
 * Middleware that formats {@see DataStream} responses as JSON and sets appropriate response headers.
 */
final class JsonDataResponseMiddleware extends AbstractDataResponseMiddleware
{
    /**
     * @param JsonFormatter $formatter The JSON formatter to use.
     */
    public function __construct(JsonFormatter $formatter = new JsonFormatter())
    {
        parent::__construct($formatter);
    }
}
