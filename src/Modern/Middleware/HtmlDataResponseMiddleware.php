<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Yiisoft\DataResponse\Modern\Formatter\HtmlFormatter;

final class HtmlDataResponseMiddleware extends AbstractDataResponseMiddleware
{
    public function __construct(HtmlFormatter $formatter = new HtmlFormatter())
    {
        $this->formatter = $formatter;
    }
}
