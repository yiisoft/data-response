<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\DataStream;

use Psr\Http\Message\StreamInterface;
use Yiisoft\DataResponse\Modern\DataStream\Formatter\StringDataFormatter;

use const SEEK_SET;

/**
 * A lazy stream that formats data only when it's being read.
 *
 * This stream wraps formatted content (string or stream) and provides
 * methods to change the data or formatter dynamically.
 */
final class DataStream implements StreamInterface
{
    private ?StreamInterface $formatted = null;

    /**
     * @param mixed $data The raw data to be formatted.
     * @param DataFormatterInterface $formatter The formatter to use.
     */
    public function __construct(
        private mixed $data,
        private DataFormatterInterface $formatter = new StringDataFormatter(),
    ) {}

    public function __toString(): string
    {
        return (string) $this->getFormatted();
    }

    /**
     * Changes the formatter and resets the stream state.
     *
     * @param DataFormatterInterface $formatter The new formatter to use.
     */
    public function changeFormatter(DataFormatterInterface $formatter): void
    {
        $this->formatter = $formatter;
        $this->resetState();
    }

    /**
     * Changes the data and resets the stream state.
     *
     * @param mixed $data The new data to be formatted.
     */
    public function changeData(mixed $data): void
    {
        $this->data = $data;
        $this->resetState();
    }

    public function close(): void
    {
        $this->getFormatted()->close();
    }

    public function detach()
    {
        return $this->getFormatted()->detach();
    }

    public function getSize(): ?int
    {
        return $this->getFormatted()->getSize();
    }

    public function tell(): int
    {
        return $this->getFormatted()->tell();
    }

    public function eof(): bool
    {
        return $this->getFormatted()->eof();
    }

    public function isSeekable(): bool
    {
        return $this->getFormatted()->isSeekable();
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $this->getFormatted()->seek($offset, $whence);
    }

    public function rewind(): void
    {
        $this->getFormatted()->rewind();
    }

    public function isWritable(): bool
    {
        return $this->getFormatted()->isWritable();
    }

    public function write(string $string): int
    {
        return $this->getFormatted()->write($string);
    }

    public function isReadable(): bool
    {
        return $this->getFormatted()->isReadable();
    }

    public function read(int $length): string
    {
        return $this->getFormatted()->read($length);
    }

    public function getContents(): string
    {
        return $this->getFormatted()->getContents();
    }

    public function getMetadata(?string $key = null)
    {
        return $this->getFormatted()->getMetadata($key);
    }

    /**
     * Gets or creates the inner stream by formatting the data.
     */
    private function getFormatted(): StreamInterface
    {
        if ($this->formatted !== null) {
            return $this->formatted;
        }

        $content = $this->formatter->format($this->data);

        $this->formatted = $content instanceof StreamInterface
            ? $content
            : new StringStream($content);

        return $this->formatted;
    }

    /**
     * Resets the stream state.
     */
    private function resetState(): void
    {
        if ($this->formatted !== null) {
            $this->formatted->close();
            $this->formatted = null;
        }
    }
}
