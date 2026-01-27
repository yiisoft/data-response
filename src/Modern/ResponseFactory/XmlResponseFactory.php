<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\Modern\Formatter\XmlFormatter;

final class XmlResponseFactory extends AbstractFormattedResponseFactory
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        XmlFormatter $formatter,
    ) {
        $this->responseFactory = $responseFactory;
        $this->formatter = $formatter;
    }
}
