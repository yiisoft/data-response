<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\DataStream;

use LogicException;
use Psr\Http\Message\StreamInterface;
use Yiisoft\DataResponse\Formatter\FormatterInterface;

use const SEEK_SET;

/**
 * A lazy stream that formats data only when it's being read.
 *
 * This stream wraps formatted content (string or stream) and provides
 * methods to change the data or formatter dynamically.
 *
 * A formatter must be set before reading the stream, otherwise a {@see LogicException} will be thrown.
 */
final class DataStream implements StreamInterface
{
    private ?StreamInterface $formatted = null;

    /**
     * @param mixed $data The raw data to be formatted.
     * @param FormatterInterface|null $formatter The formatter to use.
     */
    public function __construct(
        private mixed $data,
        private ?FormatterInterface $formatter = null,
    ) {}

    public function __toString(): string
    {
        return (string) $this->getFormatted();
    }

    /**
     * Checks whether a formatter has been set.
     *
     * @return bool Whether a formatter is set.
     */
    public function hasFormatter(): bool
    {
        return $this->formatter !== null;
    }

    /**
     * Returns the formatter.
     *
     * @return FormatterInterface|null The formatter or `null` if not set.
     */
    public function getFormatter(): ?FormatterInterface
    {
        return $this->formatter;
    }

    /**
     * Changes the formatter.
     *
     * @param FormatterInterface $formatter The new formatter.
     */
    public function changeFormatter(FormatterInterface $formatter): void
    {
        $this->formatter = $formatter;
        $this->resetState();
    }

    /**
     * Returns the raw data.
     *
     * @return mixed The raw data.
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Changes the data.
     *
     * @param mixed $data The new data.
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

    private function getFormatted(): StreamInterface
    {
        if ($this->formatted !== null) {
            return $this->formatted;
        }

        if ($this->formatter === null) {
            throw new LogicException('Formatter is not set.');
        }

        $content = $this->formatter->formatData($this->data);

        $this->formatted = $content instanceof StreamInterface
            ? $content
            : new StringStream($content);

        return $this->formatted;
    }

    private function resetState(): void
    {
        if ($this->formatted !== null) {
            $this->formatted->close();
            $this->formatted = null;
        }
    }
}
