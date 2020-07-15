<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Yiisoft\DataResponse\Formatter\HtmlDataResponseFormatter;

final class FormatDataResponseAsHtml extends FormatDataResponse
{
    public function __construct(HtmlDataResponseFormatter $responseFormatter)
    {
        parent::__construct($responseFormatter);
    }
}
