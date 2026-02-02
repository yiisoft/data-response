<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\Formatter\JsonFormatter;

final class JsonResponseFactory extends AbstractFormattedResponseFactory
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        JsonFormatter $formatter,
    ) {
        parent::__construct($responseFactory, $formatter);
    }
}
