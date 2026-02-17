<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware;

use Yiisoft\DataResponse\DataStream\DataStream;

/**
 * Middleware that applies a custom formatter to {@see DataStream} responses
 * and sets appropriate response headers.
 */
final class DataResponseMiddleware extends AbstractDataResponseMiddleware {}
