<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\DataStream;

use Psr\Http\Message\StreamInterface;
use Yiisoft\DataResponse\Modern\Formatter\FormatterInterface;
use Yiisoft\DataResponse\Modern\Formatter\PlainTextFormatter;

use const SEEK_SET;

/**
 * A lazy stream that formats data only when it's being read.
 *
 * This stream wraps formatted content (string or stream) and provides
 * methods to change the data or formatter dynamically.
 */
final class DataStream implements StreamInterface
{
    private ?StreamInterface $prepared = null;

    /**
     * @param mixed $data The raw data to be formatted.
     * @param FormatterInterface $formatter The formatter to use.
     */
    public function __construct(
        private mixed $data,
        private FormatterInterface $formatter = new PlainTextFormatter(),
    ) {}

    public function __toString(): string
    {
        return (string) $this->getPrepared();
    }

    public function format(FormatterInterface $formatter): StreamInterface
    {
        return new LazyFormattingStream($this->data, $formatter);
    }

    /**
     * Changes the data and resets the stream state.
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
        $this->getPrepared()->close();
    }

    public function detach()
    {
        return $this->getPrepared()->detach();
    }

    public function getSize(): ?int
    {
        return $this->getPrepared()->getSize();
    }

    public function tell(): int
    {
        return $this->getPrepared()->tell();
    }

    public function eof(): bool
    {
        return $this->getPrepared()->eof();
    }

    public function isSeekable(): bool
    {
        return $this->getPrepared()->isSeekable();
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $this->getPrepared()->seek($offset, $whence);
    }

    public function rewind(): void
    {
        $this->getPrepared()->rewind();
    }

    public function isWritable(): bool
    {
        return $this->getPrepared()->isWritable();
    }

    public function write(string $string): int
    {
        return $this->getPrepared()->write($string);
    }

    public function isReadable(): bool
    {
        return $this->getPrepared()->isReadable();
    }

    public function read(int $length): string
    {
        return $this->getPrepared()->read($length);
    }

    public function getContents(): string
    {
        return $this->getPrepared()->getContents();
    }

    public function getMetadata(?string $key = null)
    {
        return $this->getPrepared()->getMetadata($key);
    }

    public function getPrepared(): StreamInterface
    {
        if ($this->prepared !== null) {
            return $this->prepared;
        }

        $this->prepared = new LazyFormattingStream(
            $this->data,
            $this->formatter,
        );

        return $this->prepared;
    }

    /**
     * Resets the stream state.
     */
    private function resetState(): void
    {
        if ($this->prepared !== null) {
            $this->prepared->close();
            $this->prepared = null;
        }
    }
}
