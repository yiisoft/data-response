<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Yiisoft\DataResponse\Modern\Formatter\XmlFormatter;

final class XmlDataResponseMiddleware extends AbstractDataResponseMiddleware
{
    public function __construct(XmlFormatter $formatter = new XmlFormatter())
    {
        parent::__construct($formatter);
    }
}
