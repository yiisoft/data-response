<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;

final class FormatDataResponseAsJson extends FormatDataResponse
{
    public function __construct(JsonDataResponseFormatter $responseFormatter)
    {
        parent::__construct($responseFormatter);
    }
}
