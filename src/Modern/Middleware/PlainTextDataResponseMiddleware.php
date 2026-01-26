<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Yiisoft\DataResponse\Modern\Formatter\PlainTextFormatter;

final class PlainTextDataResponseMiddleware extends AbstractDataResponseMiddleware
{
    public function __construct(PlainTextFormatter $formatter = new PlainTextFormatter())
    {
        $this->formatter = $formatter;
    }
}
