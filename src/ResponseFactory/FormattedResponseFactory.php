<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Yiisoft\DataResponse\DataStream\DataStream;

/**
 * Factory that creates responses with a custom formatter applied to the {@see DataStream} body
 * and appropriate response headers.
 */
final class FormattedResponseFactory extends AbstractFormattedResponseFactory {}
