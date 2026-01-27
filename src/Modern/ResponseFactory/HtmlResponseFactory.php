<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\Modern\Formatter\HtmlFormatter;

final class HtmlResponseFactory extends AbstractFormattedResponseFactory
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        HtmlFormatter $formatter,
    ) {
        $this->responseFactory = $responseFactory;
        $this->formatter = $formatter;
    }
}
