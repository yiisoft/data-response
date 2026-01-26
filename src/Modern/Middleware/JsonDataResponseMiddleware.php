<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Yiisoft\DataResponse\Modern\Formatter\JsonFormatter;

final class JsonDataResponseMiddleware extends AbstractDataResponseMiddleware
{
    public function __construct(JsonFormatter $formatter = new JsonFormatter())
    {
        $this->formatter = $formatter;
    }
}
