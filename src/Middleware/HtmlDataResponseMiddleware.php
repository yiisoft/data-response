<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Yiisoft\DataResponse\Formatter\HtmlFormatter;

final class HtmlDataResponseMiddleware extends AbstractDataResponseMiddleware
{
    public function __construct(HtmlFormatter $formatter = new HtmlFormatter())
    {
        parent::__construct($formatter);
    }
}
