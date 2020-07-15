<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Yiisoft\DataResponse\Formatter\XmlDataResponseFormatter;

final class FormatDataResponseAsXml extends FormatDataResponse
{
    public function __construct(XmlDataResponseFormatter $responseFormatter)
    {
        parent::__construct($responseFormatter);
    }
}
