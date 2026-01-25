<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\DataStream;

use Psr\Http\Message\StreamInterface;

/**
 * DataFormatterInterface is the interface for formatters that convert data into formatted output.
 *
 * Formatters work only with data and return formatted content as either a string or a stream.
 */
interface DataFormatterInterface
{
    /**
     * Formats the data and returns formatted content.
     *
     * @param mixed $data The data to format.
     *
     * @return StreamInterface|string Formatted content as string or stream.
     */
    public function format(mixed $data): StreamInterface|string;
}
