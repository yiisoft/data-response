<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Yiisoft\DataResponse\Modern\Formatter\JsonResponseFormatter;

/**
 * Formats DataResponse as JSON.
 */
final class JsonDataResponseFormatter extends DataResponseFormatter
{
    public function __construct(JsonResponseFormatter $formatter = new JsonResponseFormatter())
    {
        parent::__construct($formatter);
    }
}
