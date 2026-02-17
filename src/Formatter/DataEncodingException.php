<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use LogicException;

/**
 * Exception thrown when data encoding fails during response formatting.
 *
 * This exception is typically thrown by formatters when they fail to encode
 * data into a specific format (e.g., JSON, XML).
 */
final class DataEncodingException extends LogicException {}
