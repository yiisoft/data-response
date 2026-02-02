<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\Formatter\HtmlFormatter;

final class HtmlResponseFactory extends AbstractFormattedResponseFactory
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        HtmlFormatter $formatter,
    ) {
        parent::__construct($responseFactory, $formatter);
    }
}
