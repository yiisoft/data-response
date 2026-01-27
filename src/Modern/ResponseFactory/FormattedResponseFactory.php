<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\ResponseFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\DataResponse\Modern\Formatter\FormatterInterface;

final class FormattedResponseFactory extends AbstractFormattedResponseFactory
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        FormatterInterface $formatter,
    ) {
        $this->responseFactory = $responseFactory;
        $this->formatter = $formatter;
    }
}
