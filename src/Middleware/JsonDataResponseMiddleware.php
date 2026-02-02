<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Yiisoft\DataResponse\Formatter\JsonFormatter;

final class JsonDataResponseMiddleware extends AbstractDataResponseMiddleware
{
    public function __construct(JsonFormatter $formatter = new JsonFormatter())
    {
        parent::__construct($formatter);
    }
}
