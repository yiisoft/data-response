<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Yiisoft\DataResponse\Formatter\PlainTextFormatter;

final class PlainTextDataResponseMiddleware extends AbstractDataResponseMiddleware
{
    public function __construct(PlainTextFormatter $formatter = new PlainTextFormatter())
    {
        parent::__construct($formatter);
    }
}
