<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\Formatter\PlainTextFormatter;

final class PlainTextResponseFactory extends AbstractFormattedResponseFactory
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        PlainTextFormatter $formatter,
    ) {
        parent::__construct($responseFactory, $formatter);
    }
}
