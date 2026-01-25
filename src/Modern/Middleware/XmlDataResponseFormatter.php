<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Yiisoft\DataResponse\Modern\Formatter\XmlResponseFormatter;

/**
 * Formats DataResponse as XML.
 */
final class XmlDataResponseFormatter extends DataResponseFormatter
{
    public function __construct(XmlResponseFormatter $formatter = new XmlResponseFormatter())
    {
        parent::__construct($formatter);
    }
}
