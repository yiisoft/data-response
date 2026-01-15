<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Yiisoft\DataResponse\Modern\Formatter\HtmlResponseFormatter;

/**
 * Formats DataResponse as HTML.
 */
final class HtmlDataResponseFormatter extends DataResponseFormatter
{
    public function __construct(HtmlResponseFormatter $formatter = new HtmlResponseFormatter())
    {
        parent::__construct($formatter);
    }
}
