<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\DataStream\Formatter;

use RuntimeException;
use Stringable;
use Yiisoft\DataResponse\Modern\DataStream\DataFormatterInterface;

use function is_scalar;
use function sprintf;

/**
 * Formats data as a string.
 *
 * This formatter can be used for both plain text and HTML content.
 * The actual Content-Type header is set by the response formatter that uses this data formatter.
 */
final class StringDataFormatter implements DataFormatterInterface
{
    public function format(mixed $data): string
    {
        if ($data === null) {
            return '';
        }

        if (!is_scalar($data) && !$data instanceof Stringable) {
            throw new RuntimeException(
                sprintf(
                    'Data must be either a scalar value, null, or a stringable object. %s given.',
                    get_debug_type($data),
                ),
            );
        }

        return (string) $data;
    }
}
