<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Yiisoft\DataResponse\Formatter\XmlFormatter;

final class XmlDataResponseMiddleware extends AbstractDataResponseMiddleware
{
    public function __construct(XmlFormatter $formatter = new XmlFormatter())
    {
        parent::__construct($formatter);
    }
}
