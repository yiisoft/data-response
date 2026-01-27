<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\Modern\Formatter\JsonFormatter;

final class JsonResponseFactory extends AbstractFormattedResponseFactory
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        JsonFormatter $formatter,
    ) {
        $this->responseFactory = $responseFactory;
        $this->formatter = $formatter;
    }
}
