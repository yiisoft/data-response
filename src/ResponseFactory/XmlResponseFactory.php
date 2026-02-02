<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\Formatter\XmlFormatter;

final class XmlResponseFactory extends AbstractFormattedResponseFactory
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        XmlFormatter $formatter,
    ) {
        parent::__construct($responseFactory, $formatter);
    }
}
