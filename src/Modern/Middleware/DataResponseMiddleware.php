<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Yiisoft\DataResponse\Modern\Formatter\FormatterInterface;

final class DataResponseMiddleware extends AbstractDataResponseMiddleware
{
    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }
}
