<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Yiisoft\DataResponse\Modern\Formatter\PlainTextResponseFormatter;

/**
 * Formats DataResponse as plain text.
 */
final class PlainTextDataResponseFormatter extends DataResponseFormatter
{
    public function __construct(PlainTextResponseFormatter $formatter = new PlainTextResponseFormatter())
    {
        parent::__construct($formatter);
    }
}
